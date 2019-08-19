<div style="text-align: initial">
    <?php if(isset($vars['success'])):?>
        <div class="alert alert-success text-center"><?=$vars['success'];?></div>
    <?php endif;?>
    <?php if(isset($vars['error'])):?>
        <div class="alert alert-warning text-center"><?=$vars['error'];?></div>
    <?php endif;?>
    <h2><?=GoldenSource_translate('Details');?></h2>
    <br />
    <table class="table table-striped">
        <tr>
            <td><?=GoldenSource_translate('License ID');?>:</td>
            <td><b><?=$vars['license']->id;?></b></td>
        </tr>
        <tr>
            <td><?=GoldenSource_translate('IP address');?>:</td>
            <td><b><?=$vars['license']->ip;?></b></td>
        </tr>
        <tr>
            <td><?=GoldenSource_translate('Kernel');?>:</td>
            <td><b><?=$vars['license']->kernel;?></b></td>
        </tr>
        <tr>
            <td><?=GoldenSource_translate('License key');?>:</td>
            <td><b><?=$vars['license']->licenseKey;?></b></td>
        </tr>
    </table>
    <?php if($vars['license']->status == "suspended"):?>
        <br>
        <div class="alert alert-warning text-center">
            <?=GoldenSource_translate('Your license is suspended');?>.
            <?php if(!empty($vars['license']->suspendedReason)):?>
                <?=GoldenSource_translate('Reason');?>: <?=$vars['license']->suspendedReason;?>
            <?php endif;?>
        </div>
    <?php elseif ($vars['license']->status == 'expired'):?>
        <br>
        <div class="alert alert-warning text-center">
            <?=GoldenSource_translate('Your license is expired');?>.
        </div>
    <?php else:?>
    <?php if($vars['license']->status == "active"):?>
        <hr>
        <div style="text-align: <?=GoldenSource_translate('textAlign');?>">
            <h2><?=GoldenSource_translate('Settings');?></h2>
            <?php if($vars['allowChangeIP']):?>
                <div class="row domains-row">
                <form method="post" action="clientarea.php?action=productdetails&id=<?=$vars['serviceId'];?>">
                    <input type="hidden" name="modop" value="custom">
                    <input type="hidden" name="a" value="changeIP">
                    <div class="col-xs-9">
                        <div class="input-group">
                            <span class="input-group-addon"><?=GoldenSource_translate('New IP address');?>:</span>
                            <input name="newIP" class="form-control" placeholder="192.168.1.1">
                        </div>
                    </div>
                    <div class="col-xs-3">
                        <button type="submit" id="btnCompleteProductConfig" class="btn btn-primary"><?=GoldenSource_translate('Save');?></button>
                    </div>
                </form>
                <br />
                <br />
                <br />
                <p class="text-warning"><?= sprintf(GoldenSource_translate('change_ip_price_desc'), $vars['free_change_ip'], formatCurrency($vars['settings']->change_ip_price)); ?></p>
            </div>
            <?php endif;?>
        </div>
    <?php endif;?>
    <hr>
    <h2><?=GoldenSource_translate('Installation');?></h2>
    <hr/>
    <div style="direction: ltr; text-align: left">
    <?php foreach($vars['installationHelp'] as $item):?>
        <h4><?=$item->os;?></h4>
        <div class="message markdown-content">
        <pre><code><?=$item->commands;?></code></pre>
        </div>
        <br />
    <?php endforeach;?>
    </div>
    <?php endif;?>
</div>