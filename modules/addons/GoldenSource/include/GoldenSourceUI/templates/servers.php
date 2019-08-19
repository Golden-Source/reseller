<div class="tablebg">
    <h4>Accounts</h4>
    <table style="text-align: center" id="sortabletbl1" class="datatable" width="100%">
        <tbody>
        <tr>
            <th>#</th>
            <th><?=GoldenSource_translate('Email');?></th>
            <th><?=GoldenSource_translate('Total licenses');?></th>
            <th><?=GoldenSource_translate('Credit');?></th>
            <th><?=GoldenSource_translate('Partner level');?></th>
            <th><?=GoldenSource_translate('Discount');?></th>
            <th></th>
        </tr>
        <?php foreach($vars['servers'] as $server):?>
            <tr>
                <td><?=$server->id;?></td>
                <td><?=$server->email;?></td>
                <td><?=$server->total_licenses;?></td>
                <td><?=$server->credit;?>$</td>
                <td><?=$server->partnerLevel;?></td>
                <td><?=$server->discount;?>%</td>
                <td><a href="addonmodules.php?module=GoldenSource&serverId=<?=$server->id;?>">» <?=GoldenSource_translate('Choose account');?></a></td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</div>
<?php require(__DIR__ . "/copyright.php"); ?>