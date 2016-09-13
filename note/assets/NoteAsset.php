<?php

namespace andreev1024\note\assets;

use yii\web\AssetBundle;

/**
 * Class NoteAsset
 * @package andreev1024\note
 */
class NoteAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->sourcePath = dirname(__FILE__);
        $this->js = ['js/note.js'];
        $this->depends = [
            'yii\web\JqueryAsset',
            'backend\assets_b\AppAsset',
        ];
    }
}
