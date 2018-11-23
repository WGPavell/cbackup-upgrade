## **yii2-widgets-cronui**

Widget for generating cron string

## **Installation**

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
$ php composer.phar require launcherx/yii2-widget-cronui "*"
```

or add

```
"launcherx/yii2-widget-cronui": "*"
```

to the ```require``` section of your `composer.json` file.

## **Usage**

**Initialization**  
You can use this widget in an [[yii\bootstrap\ActiveForm|ActiveForm]] using the [[yii\widgets\ActiveField::widget()|widget()]] method, for example like this:
 
```
#!php
<?= $form->field($model, 'item_id')->widget(\launcherx\cronui\CronUI::classname(), [
       'options' => [
            'class' => 'form-control',
        ],
        'pluginOptions' => [
            'dropDownMultiple' => true,
            'dropDownStyled'   => true
        ]
]) ?>
```

**Extension options**  
 
Default extension options
 
```
 'pluginOptions' => [
     'initial'           => '* * * * *',
     'dropDownMultiple'  => false,
     'dropDownStyled'    => false,
     'dropDownSizeClass' => 'col-md-2'
 ]
```

| Name                  | Type                           | Default      | Description |     
| --------------------- | ------------------------------ |------------- | --------------------------------------------------------------
|  initial              | string                         | ```'* * * * *'```  |  The initial option allows you the set the initial cron value.                                                                        
|  dropDownMultiple     | boolean                        | false        |  Allow to choose multiple values in dropdown                                                                                               
|  dropDownStyled       | boolean                        | false        |  Style dropdowns using bootstrap-select plugin                                                                                             
|  dropDownStyledFlat   | boolean                        | false        |  Style dropdowns without border radius                                                                                           
|  dropDownSizeClass    | string                         | 'col-md-2'   |  Set dropdown column size

 
## License
 
 Copyright (c) 2017 Imants Cernovs under the MIT License.