<?php

namespace lo\widgets\modal;

use yii\web\AssetBundle;

/**
 * Class ModalAsset
 * @package lo\widgets\modal
 * @author Lukyanov Andrey <loveorigami@mail.ru>
 */
class ModalAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath =  __DIR__ .'/assets';

    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\bootstrap\BootstrapAsset',
    ];

    /**
     * @inheritdoc
     */
    public $js = [
        'js/kb-modal-ajax.js',
    ];

}
