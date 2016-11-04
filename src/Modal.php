<?php

namespace lo\widgets\modal;

use yii\bootstrap\Modal as BaseModal;
use yii\helpers\Url;
use yii\web\View;

/**
 * Class Modal
 * @package lo\widgets\modal
 * @author Lukyanov Andrey <loveorigami@mail.ru>
 */
class Modal extends BaseModal
{
    const MODE_SINGLE = 'id';
    const MODE_MULTI = 'multi';

    /**
     * @var string
     */
    public $mode = self::MODE_SINGLE;

    /**
     * The selector to get url request when modal is opened for multy mode
     * @var string
     */
    public $selector;

    /**
     * The url to request when modal is opened for single mode
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
        parent::run();
        /** @var View */
        $view = $this->getView();
        $id = $this->options['id'];

        ModalAsset::register($view);

        switch ($this->mode) {
            case self::MODE_SINGLE:
                $this->registerSingleModal($id, $view);
                break;

            case self::MODE_MULTI:
                $this->registerMultyModal($id, $view);
                break;
        }

        $this->registerAutoClose($id, $view);
    }

    /**
     * @param $id
     * @param View $view
     */
    protected function registerSingleModal($id, $view)
    {
        $url = is_array($this->url) ? Url::to($this->url) : $this->url;

        $view->registerJs("
            jQuery('#$id').kbModalAjax({
                url: '$url',
                ajaxSubmit: $this->ajaxSubmit
            });
        ");
    }

    /**
     * @param $id
     * @param View $view
     */
    protected function registerMultyModal($id, $view)
    {
        $view->registerJs("
            jQuery('.btn').on('click', function(e) {
                e.preventDefault();
                $(this).attr('data-toggle', 'modal');
                $(this).attr('data-target', '#$id');
                var url = $(this).attr('href');
                
                jQuery('#$id').kbModalAjax({
                    url: url,
                    ajaxSubmit: $this->ajaxSubmit
                });
            });
        ");
    }

    /**
     * @param $id
     * @param View $view
     */
    protected function registerAutoClose($id, $view)
    {
        if ($this->autoClose) {
            $view->registerJs("
                jQuery('#$id').on('kbModalSubmit', function(event, data, status, xhr) {
                    //console.log('kbModalSubmit' + status);
                    if(status){
                        $(this).modal('toggle');
                    }
                });
            ");
        }
    }
}
