<?php
namespace needletail\needletail\controllers;

use needletail\needletail\jobs\IndexBucket;
use needletail\needletail\models\BucketModel;
use needletail\needletail\Needletail;

use Craft;
use craft\helpers\StringHelper;
use craft\web\Controller;

class BucketsController extends Controller
{
    // Properties
    // =========================================================================
    protected $redirectAfterSave = null;


    // Public Methods
    // =========================================================================

    /**
     * List all buckets
     *
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        $variables['buckets'] = Needletail::$plugin->buckets->getBuckets();

        return $this->renderTemplate('needletail/buckets/index', $variables);
    }

    /**
     * Show the create or edit form for a bucket
     *
     * @param null $bucketId
     * @return \yii\web\Response
     */
    public function actionEdit($bucketId = null)
    {
        $variables = [];

        if ($bucketId) {
            $variables['bucket'] = Needletail::$plugin->buckets->getById($bucketId);
        } else {
            $variables['bucket'] = new BucketModel();
        }

        $variables['elements'] = Needletail::$plugin->elements->getRegisteredElements();

        $files = [];
        $needletailTemplates = Craft::$app->path->getSiteTemplatesPath().'/_needletail';

        if (is_dir($needletailTemplates)) {
            $dirFiles = preg_grep('/^([^.])/', scandir($needletailTemplates));

            foreach ($dirFiles as $file) {
                $files[$file] = $file;
            }
        }

        $variables['filesList'] = $files;

        return $this->renderTemplate('needletail/buckets/_edit', $variables);
    }

    public function actionMap($bucketId)
    {
        $variables = [];
        $variables['bucket'] = Needletail::$plugin->buckets->getById($bucketId);
        return $this->renderTemplate('needletail/buckets/_map', $variables);
    }

    public function actionStart($bucketId)
    {
        Craft::$app->getSession()->setNotice(Craft::t('needletail', 'New index action successfully queued.'));

        $variables = [];
        $variables['bucket'] = Needletail::$plugin->buckets->getById($bucketId);

        Craft::$app->getQueue()->delay(0)->push(new IndexBucket([
            'bucket' => $variables['bucket']
        ]));

        return $this->redirect('needletail/buckets');
    }

    /**
     * Save a bucket
     *
     * @return \yii\web\Response|null
     * @throws \craft\errors\MissingComponentException
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionSave()
    {
        $model = $this->_getModelFromPostRequest();

        if (!Needletail::$plugin->buckets->save($model)) {
            Craft::$app->getSession()->setError(Craft::t('needletail', 'Unable to save bucket.'));

            Craft::$app->getUrlManager()->setRouteParams([
                'bucket' => $model,
            ]);

            return null;
        }

        Needletail::$plugin->connection->initBucket($model->handle);

        Craft::$app->getSession()->setNotice(Craft::t('needletail', 'Bucket saved.'));

        return $this->redirectToPostedUrl($model);
    }

    /**
     * Delete a bucket
     *
     * @return \yii\web\Response
     * @throws \yii\web\BadRequestHttpException
     */
    public function actionDelete()
    {
        $request = Craft::$app->getRequest();

        $this->requirePostRequest();

        $bucketId = $request->getRequiredBodyParam('id');

        Needletail::$plugin->buckets->deleteById($bucketId);

        return $this->asJson(['success' => true]);
    }


    // Private Methods
    // =========================================================================
    private function _getModelFromPostRequest()
    {
        $request = Craft::$app->getRequest();

        $this->requireCpRequest();
        $this->requirePostRequest();

        if ($request->getBodyParam('bucketId')) {
            $bucket = Needletail::$plugin->buckets->getById($request->getBodyParam('bucketId'));
        } else {
            $bucket = new BucketModel();
        }
        $params = [
            'name', 'handle', 'elementType', 'elementData', 'siteId', 'fieldMapping', 'customMappingFile', 'mappingTwigFile'
        ];

        foreach ( $params as $param )
        {
            if ( $fromRequest = $request->getBodyParam($param) )
                $bucket->{$param} = $fromRequest;
        }

        return $bucket;
    }
}
