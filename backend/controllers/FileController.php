<?php
/**
 * FileController class File
 *
 * @author yang <css3@qq.com>
 */

/**
 * FileController
 *
 * @author yang <css3@qq.com>
 * @package backend.controllers
 */
class FileController extends Controller
{
	/**
	 * 首页
	 */
	public function actionIndex()
	{
		if (isset($_POST['doaction'])) {
			$action = !empty($_POST['action']) ? $_POST['action'] : null;
			$ids = !empty($_POST['ids']) ? $_POST['ids'] : null;
			if ($action && $ids)
				$this->processBulkAction($action, $ids);
			$this->redirect(array('index'));
			return;
		}

		$model=new File('search');
		$model->unsetAttributes();
		if(isset($_GET['File']))
			$model->attributes=$_GET['File'];

		$model->bundle = 'File';

		$this->render('index',array(
			'model'=>$model,
		));
	}

	/**
	 * 查看文件
	 * @param integer $id
	 */
	public function actionView($id)
	{
		$this->render('view', array('model'=>$this->loadModel($id)));
	}

	/**
	 * 更新文件
	 * @param integer $id
	 */
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);

		$isAjax = Yii::app()->getRequest()->getIsAjaxRequest();

		if(isset($_POST['File'])) {
			$model->attributes=$_POST['File'];

			if($model->save()) {
				if ($isAjax) {
					Yii::app()->end();
				}
				Yii::app()->getUser()->setFlash('success', '更新文件成功');
				$this->redirect(array('view','id'=>$model->id));
			}
		}

		if ($isAjax) {
			$this->renderPartial('_form', array('model'=>$model, 'isAjax'=>true));
		} else {
			$this->render('update',array('model'=>$model, 'isAjax'=>false));
		}
	}

	/**
	 * 删除文件
	 * @param integer $id
	 * @throws CHttpException
	 */
	public function actionDelete($id)
	{
		$isAjax = Yii::app()->getRequest()->getIsAjaxRequest() || isset($_GET['ajax']);

		if(Yii::app()->request->isPostRequest) {
			$model = $this->loadModel($id);

			if ($model->bundle === 'File') {
				$result = $model->delete();
			}

			if(!$isAjax) {
				if ($result) {
					Yii::app()->getUser()->setFlash('success', '删除文件成功');
				}
				$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
			} else {
				header('Content-Type:text/json');
				if ($result !== false) {
					echo CJSON::encode(array('error'=>0, 'message'=>'删除文件成功'));
				} else {
					echo CJSON::encode(array('error'=>1, 'message'=>'删除文件失败'));
				}
				Yii::app()->end();
			}
		} else
			throw new CHttpException(405,'无效的请求，请重试');
	}

	/**
	 * 清理文件
	 * @throws CHttpException
	 */
	public function actionClean()
	{
		if(Yii::app()->request->isPostRequest) {
			if ($count = File::clean())
				Yii::app()->getUser()->setFlash('success', sprintf('已清理掉%d个文件', $count));
			else
				Yii::app()->getUser()->setFlash('success', '没有文件需要清理');

			$this->redirect(array('index'));
		} else
			throw new CHttpException(405,'无效的请求，请重试');
	}

	/**
	 * 上传文件
	 * @param mixed $model
	 * @param string $field 上传字段
	 */
	public function actionUpload($model=null, $field=null)
	{
		if ($model === null || $field === null) {
			$modelClass = 'File';
			$field = 'file';
		} else {
			$modelClass = $model;
		}

		$model = new $modelClass('upload');

		if (isset($_POST['plupload'])) {
			$_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
		}

		$isIframe = false;
		if (isset($_GET['CKEditor'])) {
			$model->$field = CUploadedFile::getInstanceByName('upload');
			$isIframe = true;
			$funcNum = $_GET['CKEditorFuncNum'];
		}

		$isAjax = Yii::app()->getRequest()->getIsAjaxRequest() || (isset($_POST['ajax']) && $_POST['ajax'] === 'file-upload');
		if (isset($_POST[$modelClass]) || $isIframe) {
			$file = $model->upload($field);
			if ($file !== false) {
				if ($isAjax) {
					header('Content-Type:text/json');
					if (isset($_GET['jquery-file-upload'])) {
						echo CJSON::encode(array(
							array(
								'id'=>$file->id,
								'name'=>$file->filename,
								'size'=>$file->filesize,
								'url'=>$file->getUrl(),
								'thumbnailUrl'=>$file->getImageUrl(File::IMAGE_THUMBNAIL),
								'deleteUrl'=>$this->createUrl('delete', array('id'=>$file->id)),
								'deleteType'=>'POST',
							)
						));
					} else {
						echo CJSON::encode(array('error'=>0, 'file'=>$file->toArray()));
					}
				} elseif ($isIframe) {
					$url = $file->getUrl();
					$message= '上传文件成功';
					echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$url}', '{$message}');</script>";
				} else {
					Yii::app()->getUser()->setFlash('success', '文件上传成功');
					$this->redirect(array('view', 'id'=>$file->id));
				}
			} else {
				if ($isAjax) {
					header('Content-Type:text/json');
					if (isset($_GET['jquery-file-upload'])) {
						echo CJSON::encode(array(
							array(
								'error'=>$model->getError($field),
							)
						));
					} else {
						echo CJSON::encode(array('error'=>1, 'message'=>$model->getError($field)));
					}
				} elseif ($isIframe) {
					$message = $model->getError('file');
					echo "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '', '{$message}');</script>";
				}
			}
		}

		if ($isAjax || $isIframe) {
			Yii::app()->end();
		}

		$this->render('upload',array(
			'model'=>$model,
		));
	}

	/**
	 * 浏览文件
	 */
	public function actionBrowse()
	{
		$this->layout = '//layouts/none';
		$model=new File('search');
		$model->unsetAttributes();

		if(isset($_GET['File']))
			$model->attributes=$_GET['File'];

		$model->bundle = 'File';

		$this->render('browse',array(
			'model'=>$model,
		));
	}

	/**
	 * 读取文件模型
	 * @param integer $id
	 * @throws CHttpException
	 * @return File
	 */
	public function loadModel($id)
	{
		$model=File::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'页面没有找到');
		return $model;
	}

	/**
	 * 批量处理
	 * @param string $action
	 * @param mixed $ids
	 */
	protected function processBulkAction($action, $ids)
	{
		$ids = (array) $ids;
		$count = 0;
		switch ($action) {
			case 'delete':
				foreach (File::model()->findAllByPk($ids) as $model) {
					if ($model->bundle == 'File' && $model->delete()) {
						$count++;
					}
				}
				Yii::app()->getUser()->setFlash('success', sprintf('已删除%d个文件', $count));
				break;
		}
	}
}
