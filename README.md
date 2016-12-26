# Yii2-modal-ajax
[![Latest Stable Version](https://poser.pugx.org/loveorigami/yii2-modal-ajax/v/stable)](https://packagist.org/packages/loveorigami/yii2-modal-ajax) 
[![Total Downloads](https://poser.pugx.org/loveorigami/yii2-modal-ajax/downloads)](https://packagist.org/packages/loveorigami/yii2-modal-ajax)
[![License](https://poser.pugx.org/loveorigami/yii2-modal-ajax/license)](https://packagist.org/packages/loveorigami/yii2-modal-ajax)

A wrapper around Yii2 Bootstrap Modal for using an ActiveForm via AJAX inside.  

## Installation
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run
```sh
$ php composer.phar require --prefer-dist loveorigami/yii2-modal-ajax "@dev"
```
or add
```
"loveorigami/yii2-modal-ajax": "@dev"
```
to the require section of your composer.json file.

## Usage

### Controller
Extend your basic logic with ajax render and save capabilities:
```php
public function actionCreate()
{
    $model = new Company();

    if ($model->load(Yii::$app->request->post())) {
        if ($model->save()) {             
            return $this->redirect(['view', 'id' => $model->id]);             
        }
    }

    return $this->render('create', [
        'model' => $model,
    ]);
}
```
to
```php
public function actionCreate()
{
    $model = new Company();

    if ($model->load(Yii::$app->request->post())) {
        if ($model->save()) {             
            if (Yii::$app->request->isAjax) {
                // JSON response is expected in case of successful save
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                return ['success' => true];
            }
            return $this->redirect(['view', 'id' => $model->id]);             
        }
    }

    if (Yii::$app->request->isAjax) {
        return $this->renderAjax('create', [
            'model' => $model,
        ]);
    } else {
        return $this->render('create', [
            'model' => $model,
        ]);
    }
}
```


### View
```php
use lo\widgets\modal\ModalAjax;

echo ModalAjax::widget([
    'id' => 'createCompany',
    'header' => 'Create Company',
    'toggleButton' => [
        'label' => 'New Company',
        'class' => 'btn btn-primary pull-right'
    ],
    'url' => Url::to(['/partner/default/create']), // Ajax view with form to load
    'ajaxSubmit' => true, // Submit the contained form as ajax, true by default
    // ... any other yii2 bootstrap modal option you need
]);
```

## Usage in grid 

### Index View - Create (Single Modal Mode)
```php
use lo\widgets\modal\ModalAjax;

echo ModalAjax::widget([
    'id' => 'createCompany',
    'header' => 'Create Company',
    'toggleButton' => [
        'label' => 'New Company',
        'class' => 'btn btn-primary pull-right'
    ],
    'url' => Url::to(['/partner/default/create']), // Ajax view with form to load
    'ajaxSubmit' => true, // Submit the contained form as ajax, true by default

    'options' => ['class' => 'header-primary'],
    'autoClose' => true,
    'pjaxContainer' => '#grid-company-pjax',

]);
```

### Index View - Update (Multi Modal Mode)
Grid example with data-scenario
```html
<a class="btn" href="/site/update?id=10" title="Edit ID-10" data-scenario="hello">Hello</a>
<a class="btn" href="/site/update?id=20" title="Edit ID-20" data-scenario="goodbye">Goodbye</a>
```
Modal Ajax with events
```php
use lo\widgets\modal\ModalAjax;

echo ModalAjax::widget([
    'id' => 'updateCompany',
    'selector' => 'a.btn' // all buttons in grid view with href attribute
    'ajaxSubmit' => true, // Submit the contained form as ajax, true by default

    'options' => ['class' => 'header-primary'],
    'pjaxContainer' => '#grid-company-pjax',
    'events'=>[
        ModalAjax::EVENT_MODAL_SUBMIT => new \yii\web\JsExpression("
            function(event, data, status, xhr, selector) {
                if(status){
                    if(selector.data('scenario') == 'hello'){
                        alert('hello');
                    } else {
                        alert('goodbye');
                    }
                    $(this).modal('toggle');
                }
            }
        ")
    ]

]);
```


## Plugin Events

On top if the basic twitter bootstrap modal events the following events are triggered


### `kbModalBeforeShow` (ModalAjax::EVENT_BEFORE_SHOW)
This event is triggered right before the view for the form is loaded. Additional parameters available with this event are:
- `xhr`: _XMLHttpRequest_, the jQuery XML Http Request object used for this transaction.
- `settings`: _object_, the jQuery ajax settings for this transaction.

```js
$('#createCompany').on('kbModalBeforeShow', function(event, xhr, settings) {
    console.log('kbModalBeforeShow');
});
```

### `kbModalShow` (ModalAjax::EVENT_MODAL_SHOW)
This event is triggered after the view for the form is successful loaded. Additional parameters available with this event are:
- `data`: _object_, the data object sent via server's response.
- `status`: _string_, the jQuery AJAX success text status.
- `xhr`: _XMLHttpRequest_, the jQuery XML Http Request object used for this transaction.

```js
$('#createCompany').on('kbModalShow', function(event, data, status, xhr) {
    console.log('kbModalShow');
});
```

### `kbModalBeforeSubmit` (ModalAjax::EVENT_BEFORE_SUBMIT)
This event is triggered right before the form is submitted. Additional parameters available with this event are:
- `xhr`: _XMLHttpRequest_, the jQuery XML Http Request object used for this transaction.
- `settings`: _object_, the jQuery ajax settings for this transaction.

```js
$('#createCompany').on('kbModalBeforeSubmit', function(event, xhr, settings) {
    console.log('kbModalBeforeSubmit');
});
```

### `kbModalSubmit` (ModalAjax::EVENT_MODAL_SUBMIT)
This event is triggered after the form is successful submitted. Additional parameters available with this event are:
- `data`: _object_, the data object sent via server's response.
- `status`: _string_, the jQuery AJAX success text status.
- `xhr`: _XMLHttpRequest_, the jQuery XML Http Request object used for this transaction.
- `selector`: _object_, the jQuery selector for embed logic after submit in multi Modal.

```js
$('#createCompany').on('kbModalSubmit', function(event, data, status, xhr, selector) {
    console.log('kbModalSubmit');
    // You may call pjax reloads here
});
```
