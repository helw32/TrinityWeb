<div class="panel panel-default">
    <!-- todo: change image url with php code -->
    <div class="panel-heading">
        <h2 class="text-center">Setup Wizard</h2>
    </div>

    <div class="panel-body  text-center">

        <p class="lead"><strong>Welcome to the TrinityWeb Installer</p>

        <p>This wizard will install and configure your application.<br><br>To continue, click Next.</p>

        <div class="text-center">
            <br/>
            <?= \yii\helpers\Html::a("Next" . ' <i class="fa fa-arrow-circle-right"></i>', ['setup/prerequisites'],
                ['class' => 'btn btn-lg btn-primary']) ?>
            <br/><br/>
        </div>
    </div>
</div>