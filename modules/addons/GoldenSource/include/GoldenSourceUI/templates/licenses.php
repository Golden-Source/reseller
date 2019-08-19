<a href="addonmodules.php?module=GoldenSource">« <?=GoldenSource_translate('Back to accounts');?></a>
<br>
<br>
<ul class="nav nav-tabs admin-tabs" role="tablist">
<li class="<?=($vars['activeTab'] == 'search') ? 'active' : '';?>">
    <a class="tab-top" href="#search" role="tab" data-toggle="tab" id="tabLink1" data-tab-id="1" aria-expanded="true"><?=GoldenSource_translate('Search/Filter');?></a>
</li>
<li class="<?=($vars['activeTab'] == 'products') ? 'active' : '';?>">
    <a class="tab-top" href="#products" role="tab" data-toggle="tab" id="tabLink1" data-tab-id="1" aria-expanded="true"><?=GoldenSource_translate('Products');?></a>
</li>
<li class="<?=($vars['activeTab'] == 'instructions') ? 'active' : '';?>">
    <a class="tab-top" href="#instructions" role="tab" data-toggle="tab" id="tabLink1" data-tab-id="1" aria-expanded="true"><?=GoldenSource_translate('Instructions');?></a>
</li>
<li class="<?=($vars['activeTab'] == 'addProducts') ? 'active' : '';?>">
    <a class="tab-top" href="#addProducts" onclick="window.location.href='addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>&addProducts=1';" role="tab" data-toggle="tab" id="tabLink1" data-tab-id="1" aria-expanded="true"><?=GoldenSource_translate('Add Products');?></a>
</li>
<li class="<?=($vars['activeTab'] == 'settings') ? 'active' : '';?>">
    <a class="tab-top" href="#settings" role="tab" data-toggle="tab" id="tabLink1" data-tab-id="1" aria-expanded="true"><?=GoldenSource_translate('Settings');?></a>
</li>
</ul>
<div class="tab-content admin-tabs">
  <div class="tab-pane <?=($vars['activeTab'] == 'search') ? 'active' : '';?>" id="search">
       <form action="addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>&search=1" method="post" _lpchecked="1">
        <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
            <tbody>
            <tr>
                <td width="200" class="fieldlabel"><?=GoldenSource_translate('IP address');?></td>
                <td class="fieldarea">
                    <input type="text" name="ip" size="40" value="<?=$vars['criteria']['ip'];?>" class="form-control">
                </td>
            </tr>
            <tr>
                <td class="fieldlabel"><?=GoldenSource_translate('Status');?></td>
                <td class="fieldarea">
                    <select id="multi-view" name="status[]" class="form-control selectize-multi-select" multiple="multiple" data-value-field="id" placeholder="Any Status">
                        <option value="active" <?=(in_array('active', $vars['criteria']['status'])) ? 'selected' : '';?>><?=GoldenSource_translate('Active');?></option>
                        <option value="suspended" <?=(in_array('suspended', $vars['criteria']['status'])) ? 'selected' : '';?>><?=GoldenSource_translate('Suspended');?></option>
                        <option value="expired" <?=(in_array('expired', $vars['criteria']['status'])) ? 'selected' : '';?>><?=GoldenSource_translate('Expired');?></option>
                    </select>
                </td>
            </tr>
        </tbody></table>
        <div class="btn-container">
            <input type="submit" value="<?=GoldenSource_translate('Search/Filter');?>" class="btn btn-primary">
        </div>
        </form>
  </div>
  <div class="tab-pane <?=(($vars['activeTab'] == 'instructions') ? 'active' : '');?>" id="instructions">
        <h2><?=GoldenSource_translate('Instructions');?></h2>
        <ul>
            <?php foreach($vars['products'] as $product):?>
                <li><a target="_blank" href="addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>&productId=<?=$product->id;?>">» <?=$product->fullName;?></a></li>
            <?php endforeach;?>
        </ul>
  </div>
  <div class="tab-pane <?=(($vars['activeTab'] == 'settings') ? 'active' : '');?>" id="settings">
       <?php require(__DIR__ . "/settings.php"); ?>
  </div>
  <div class="tab-pane <?=(($vars['activeTab'] == 'products') ? 'active' : '');?>" id="products">
       <?php require(__DIR__ . "/products.php"); ?>
  </div>
  <div class="tab-pane <?=(($vars['activeTab'] == 'addProducts') ? 'active' : '');?>" id="addProducts">
        <?=$vars['addProducts'];?>
  </div>
</div>
<br>
<?php require(__DIR__ . "/header.php"); ?>
<div class="tablebg">
    <h4><?=GoldenSource_translate('Licenses');?> (<?=$vars['information']->total_licenses;?>)</h4>
    <?php if(isset($vars['success']) && !empty($vars['success'])):?>
        <div class="successbox">
            <strong><span class="title"><?=GoldenSource_translate('Changes Saved Successfully!');?></span></strong>
            <br>
            <?=$vars['success'];?>
        </div><br>
    <?php endif;?>
    <?php if(isset($vars['error']) && !empty($vars['error'])):?>
        <div class="errorbox"><strong><span class="title"><?=GoldenSource_translate('Error');?></span></strong><br><?=$vars['error'];?></div><br>
        <?php endif;?>
        <div class="table-responsive">
    <table style="text-align: center;" id="sortabletbl1" class="datatable licenses" width="100%">
        <tbody>
        <tr>
            <th style="width: 75px">#</th>
            <th style="width: 120px"><?=GoldenSource_translate('IP');?></th>
            <th style="width: 80px"><?=GoldenSource_translate('Change IP');?></th>
            <th><?=GoldenSource_translate('Hostname');?></th>
            <th><?=GoldenSource_translate('Cost');?></th>
            <th style="width: 100px"><?=GoldenSource_translate('status');?></th>
            <th><?=GoldenSource_translate('Due date');?></th>
            <th style="width: 55px"><?=GoldenSource_translate('Service');?></th>
            <th style="width: 60px"></th>
        </tr>
        <?php
            $previousProductId = null;
            $previousCount = 0;
        ?>
        <?php foreach($vars['licenses'] as $license):?>
            <?php if($previousCount > 10 || $previousProductId === null || $license->productId <> $previousProductId):?>
            <?php $previousProductId = $license->productId; $previousCount = 0;?>
            <tr>
                <td colspan="9" style="background-color:#f3f3f3;direction:ltr">
                    <?=(!isset($vars['products'][$license->productId]) ? '???' : $vars['products'][$license->productId]->fullName);?>
                </td>
            </tr>
            <?php endif;?>
            <?php $previousCount++;?>
            <tr class="<?=$license->status;?>">
                <td><?=$license->id;?></td>
                <td><?=$license->ip;?></td>
                <td><?=$license->changeIP;?>/3</td>
                <td><?=$license->hostname;?></td>
                <?php if(isset($vars['products'][$license->productId])):?>
                <td><?=$vars['products'][$license->productId]->priceWithDiscount($license->cycle);?>$ (<?=GoldenSource_translate($license->cycle);?>)</td>
                <?php else:?>
                <td>???</td>
                <?php endif;?>
                <td><?=GoldenSource_translate(ucfirst($license->status));?></td>
                <?php if($license->status == 'expired'):?>
                    <td><?=$license->renewDate();?></td>
                <?php else:?>
                    <td><?=$license->renewDate();?> (<?=$license->remainingDays();?> <?=GoldenSource_translate('days');?>)</td>
                <?php endif;?>
                <td><?=$license->client;?></td>
                <td style="height: 100%">
                    <a onclick="return confirm('<?=GoldenSource_translate('Are you sure you want to extend this license?');?>');" href="addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>&extendLicense=<?=$license->id;?>&c=<?=$vars['sessionChecker'];?>">
                        <img src="images/icons/add.png" border="0" align="absmiddle">
                    </a>
                    &nbsp;
                    <a href="addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>&licenseId=<?=$license->id;?>">
                        <img src="images/edit.gif" width="16" height="16" border="0" alt="Edit">
                    </a>
                </td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</div>
</div>
