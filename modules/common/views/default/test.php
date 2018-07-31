<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'login-form',
    'options' => ['class' => 'form-horizontal','enctype' => 'multipart/form-data'],
    'action'=>'/common/default/getfiles',
    'method'=>'post'
]) ?>


    <div class="form-group">
        <div class="col-lg-2">
            <?php echo  $form->field($model, 'imgFile[]')->fileInput(['multiple' => true])->label(false); ?>
        </div>
        <div class="col-lg-6">
            <?php echo Html::submitButton('保存', ['class' => 'btn btn-primary']) ?>
        </div>
    </div>
<?php ActiveForm::end() ?>