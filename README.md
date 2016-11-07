# yii2-modal-ajax

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
use lo\widgets\modal\AjaxModal;

echo AjaxModal::widget([
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

### Index View - Create
```php
use lo\widgets\modal\AjaxModal;

echo AjaxModal::widget([
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
    'pjaxContainer' => '#grid-company-pjax'

]);
```

### Index View - Update
```php
use lo\widgets\modal\AjaxModal;

echo AjaxModal::widget([
    'id' => 'updateCompany',
    'selector' => 'a.btn' // all buttons in grid view with href attribute
    'ajaxSubmit' => true, // Submit the contained form as ajax, true by default

    'options' => ['class' => 'header-primary'],
    'autoClose' => true,
    'pjaxContainer' => '#grid-company-pjax'

]);
```


## Plugin Events

On top if the basic twitter bootstrap modal events the following events are triggered


### `kbModalBeforeShow`
This event is triggered right before the view for the form is loaded. Additional parameters available with this event are:
- `xhr`: _XMLHttpRequest_, the jQuery XML Http Request object used for this transaction.
- `settings`: _object_, the jQuery ajax settings for this transaction.

```js
$('#createCompany').on('kbModalBeforeShow', function(event, xhr, settings) {
    console.log('kbModalBeforeShow');
});
```

### `kbModalShow`
This event is triggered after the view for the form is successful loaded. Additional parameters available with this event are:
- `data`: _object_, the data object sent via server's response.
- `status`: _string_, the jQuery AJAX success text status.
- `xhr`: _XMLHttpRequest_, the jQuery XML Http Request object used for this transaction.

```js
$('#createCompany').on('kbModalShow', function(event, data, status, xhr) {
    console.log('kbModalShow');
});
```

### `kbModalBeforeSubmit`
This event is triggered right before the form is submitted. Additional parameters available with this event are:
- `xhr`: _XMLHttpRequest_, the jQuery XML Http Request object used for this transaction.
- `settings`: _object_, the jQuery ajax settings for this transaction.

```js
$('#createCompany').on('kbModalBeforeSubmit', function(event, xhr, settings) {
    console.log('kbModalBeforeSubmit');
});
```

### `kbModalSubmit`
This event is triggered after the form is successful submitted. Additional parameters available with this event are:
- `data`: _object_, the data object sent via server's response.
- `status`: _string_, the jQuery AJAX success text status.
- `xhr`: _XMLHttpRequest_, the jQuery XML Http Request object used for this transaction.

```js
$('#createCompany').on('kbModalSubmit', function(event, data, status, xhr) {
    console.log('kbModalSubmit');
    // You may call pjax reloads here
});
```
