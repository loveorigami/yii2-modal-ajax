<?php

namespace lo\widgets\modal;

use yii\base\InvalidConfigException;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/**
 * Class ModalAjax
 * @package lo\widgets\modal
 * @author Lukyanov Andrey <loveorigami@mail.ru>
 */
class ModalAjax extends Modal
{
    const MODE_SINGLE = 'id';
    const MODE_MULTI = 'multi';

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
     * reload pjax container after ajaxSubmit
     * @var string
     */
    public $pjaxContainer;

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
     * @var string
     */
    protected $mode = self::MODE_SINGLE;

    /**
     * Renders the header HTML markup of the modal
     * @return string the rendering result
     */
    protected function renderHeader()
    {
        $button = $this->renderCloseButton();
        if ($button !== null) {
            $this->header = $button . "\n<span>" . $this->header."</span>\n";
        }
        if ($this->header !== null) {
            Html::addCssClass($this->headerOptions, ['widget' => 'modal-header']);
            return Html::tag('div', "\n" . $this->header . "\n", $this->headerOptions);
        } else {
            return null;
        }
    }

    /**
     * @inheritdocs
     */
    public function init()
    {
        parent::init();
        if (!$this->url && !$this->selector) {
            throw new InvalidConfigException('Not specified property "Url" or "Selector"');
        }

        if ($this->selector) {
            $this->mode = self::MODE_MULTI;
        }
    }

    /**
     * @inheritdocs
     */
    public function run()
    {
        parent::run();
        /** @var View */
        $view = $this->getView();
        $id = $this->options['id'];

        ModalAjaxAsset::register($view);

        switch ($this->mode) {
            case self::MODE_SINGLE:
                $this->registerSingleModal($id, $view);
                break;

            case self::MODE_MULTI:
                $this->registerMultyModal($id, $view);
                break;
        }

        $this->registerAutoClose($id, $view);
        $this->registerPjaxReload($id, $view);
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
            jQuery('body').on('click', '$this->selector', function(e) {
                e.preventDefault();
                $(this).attr('data-toggle', 'modal');
                $(this).attr('data-target', '#$id');
                
                var bs_url = $(this).attr('href');
                var title = $(this).attr('title');
                
                if (!title) title = ' ';
                
                jQuery('#$id').find('.modal-header span').html(title);
                
                jQuery('#$id').kbModalAjax({
                    url: bs_url,
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
                    if(status){
                        $(this).modal('toggle');
                    }
                });
            ");
        }
    }

    /**
     * @param $id
     * @param View $view
     */
    protected function registerPjaxReload($id, $view)
    {
        if ($this->pjaxContainer) {
            $view->registerJs("
                jQuery('#$id').on('kbModalSubmit', function(event, data, status, xhr) {
                    if(status){
                        $.pjax.reload({
                            container : '$this->pjaxContainer'
                        });
                    }
                });
            ");
        }
    }
}
