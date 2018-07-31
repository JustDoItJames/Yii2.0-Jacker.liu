<div class="common-default-index">
    <?php echo time();echo '<br />';?>
    <h1><?= $this->context->action->uniqueId ?></h1>
    <!--片段缓存-->
<!--    --><?php //if($this->beginCache($this->context->action->uniqueId)){
//            echo time();echo '<br />';
//            echo $this->renderDynamic('return time();');
//            $this->endCache();
//        }
//    ?>



    <!--小部件-->
<!--    --><?php //echo app\components\Widgets\Menu::widget(['activateItems'=>'测试一下小部件']);?>
    
    <p>
        This is the view content for action "<?= $this->context->action->id ?>".
        The action belongs to the controller "<?= get_class($this->context) ?>"
        in the "<?= $this->context->module->id ?>" module.
    </p>
    <p>
        You may customize this page by editing the following file:<br>
        <code><?= __FILE__ ?></code>
    </p>
</div>
