<?php

namespace lo\widgets\modal;

use yii\bootstrap\Modal as BaseModal;

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
     * @inheritdocs
     */
    public function run()
    {
        $view = $this->getView();
        parent::run();

        ModalAsset::register($view);

        $id = $this->options['id'];
        $ajaxSubmit = $this->ajaxSubmit ? 'true' : 'false';
        $js = <<<JS
        jQuery('#$id').kbModalAjax({
            url: '{$this->url}',
            ajaxSubmit: {$ajaxSubmit},
        });
JS;
        $view->registerJs($js);
    }
}
