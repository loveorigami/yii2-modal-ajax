<?php

namespace lo\widgets\modal;

use yii\bootstrap\Modal as BaseModal;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Class Modal
 * @package lo\widgets\modal
 * @author Lukyanov Andrey <loveorigami@mail.ru>
 */
class Modal extends BaseModal
{
    /**
     * The url to request when modal is opened
     * @var string
     */
    public $url;

    /**
     * Submit the form via ajax
     * @var boolean
     */
    public $ajaxSubmit = true;

    /**
     * Submit the form via ajax
     * @var boolean
     */
    public $autoClose = false;

    /**
     * @inheritdocs
     */
    public function run()
    {
        $view = $this->getView();
        parent::run();

        ModalAsset::register($view);
        $id = $this->options['id'];

        $config['ajaxSubmit'] = $this->ajaxSubmit;
        $config['url'] = is_array($this->url) ? Url::to($this->url) : $this->url;

        $options = Json::encode($config);

        $view->registerJs("jQuery('#$id').kbModalAjax($options);");

        if ($this->autoClose) {
            $js = "
            jQuery('#$id').on('kbModalSubmit', function(event, data, status, xhr) {
                console.log('kbModalSubmit' + status);
                if(status){
                    $(this).modal('toggle');
                }
            });";
            $view->registerJs($js);
        }
    }
}
