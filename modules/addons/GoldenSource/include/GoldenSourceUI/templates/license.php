<a href="addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>">« <?=GoldenSource_translate('Back to licenses');?></a>
<br>
<br>
<ul class="nav nav-tabs admin-tabs" role="tablist">
    <li class="dropdown pull-right tabdrop hide"><a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false"><i class="icon-align-justify"></i> <b class="caret"></b></a><ul class="dropdown-menu"></ul></li>
    <li class="<?=($vars['activeTab'] == 'details') ? 'active' : '';?>">
        <a class="tab-top" href="#details" role="tab" data-toggle="tab" id="tabLink1" data-tab-id="1" aria-expanded="true"><?=GoldenSource_translate('License');?> #<?=$vars['license']->id;?></a>
    </li>
</ul>
<div class="tab-content admin-tabs">
  <div class="tab-pane <?=($vars['activeTab'] == 'details') ? 'active' : '';?>" id="details">
    <?php if(isset($vars['success']) && !empty($vars['success'])):?>
            <div class="successbox">
                <strong><span class="title"><?=GoldenSource_translate('Changes Saved Successfully!');?></span></strong>
                <br>
                <?=$vars['success'];?>
            </div>
        <?php endif;?>
        <?php if(isset($vars['error']) && !empty($vars['suerrorccess'])):?>
            <div class="errorbox"><strong><span class="title"><?=GoldenSource_translate('Error');?></span></strong><br><?=$vars['error'];?></div>
            <?php endif;?>
            <div class="row client-summary-panels">
            <div class="col-lg-3 col-sm-6">
                <div class="clientssummarybox">
                        <div class="title"><?=GoldenSource_translate('License Information');?></div>
                        <table class="clientssummarystats license <?=$vars['license']->status;?>" cellspacing="0" cellpadding="2">
                            <tbody>
                            <tr>
                                <td><?=GoldenSource_translate('License ID');?></td>
                                <td><b><?=$vars['license']->id;?></b></td>
                            </tr>
                            <tr>
                                <td><?=GoldenSource_translate('Product');?></td>
                                <td>
                                <b>
                                    <?php
                                    $fullName = $vars['license']->product()->fullName;
                                    $fullName = str_replace('-', '<br />', $fullName);
                                    echo $fullName;
                                    ?>
                                </b>
                                </td>
                            </tr>
                            <tr>
                                <td><?=GoldenSource_translate('IP address');?></td>
                                <td><b><?=$vars['license']->ip;?></b></td>
                            </tr>
                            <tr>
                                <td><?=GoldenSource_translate('Hostname');?></td>
                                <td><b><?=$vars['license']->hostname;?></b></td>
                            </tr>
                            <tr>
                                <td><?=GoldenSource_translate('Kernel');?></td>
                                <td><b><?=$vars['license']->kernel;?></b></td>
                            </tr>
                            <tr>
                                <td><?=GoldenSource_translate('License key');?></td>
                                <td><b><?=$vars['license']->licenseKey;?></b></td>
                            </tr>
                            <tr >
                                <td><?=GoldenSource_translate('status');?></td>
                                <td><b><?=GoldenSource_translate(ucfirst($vars['license']->status));?></b></td>
                            </tr>
                            <?php if ($vars['license']->status == 'suspended' && !empty($vars['license']->suspendedReason)) :?>
                                <tr >
                                <td><?=GoldenSource_translate('Suspension Reason');?></td>
                                <td><b><?=$vars['license']->suspendedReason;?></b></td>
                            </tr>
                            <?php endif;?>
                            <tr>
                                <td><?=GoldenSource_translate('renewDate');?></td>
                                <td><b><?=$vars['license']->renewDate();?></b></td>
                            </tr>
                            <tr>
                                <td><?=GoldenSource_translate('Time left');?></td>
                                <td><b><?=$vars['license']->remainingDays(true);?></b></td>
                            </tr>
                            <tr>
                                <td><?=GoldenSource_translate('Change IP');?></td>
                                <td><b><?=$vars['license']->changeIP;?>/3</b></td>
                            </tr>
                            <tr>
                                <td><?=GoldenSource_translate('Cost');?></td>
                                <td><b>$<?=$vars['license']->product()->priceWithDiscount($vars['license']->cycle);?> (<?=GoldenSource_translate($vars['license']->cycle);?>)</b></td>
                            </tr>
                        </tbody>
                        </table>
                        <ul>
                            <li><a href="addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>&licenseId=<?=$vars['license']->id;?>&c=<?=$vars['sessionChecker'];?>&extend=1"><img src="images/icons/add.png" border="0" align="absmiddle"> <?=GoldenSource_translate('Extend license');?></a></li>
                        </ul>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="clientssummarybox">
                    <div class="title"><?=GoldenSource_translate('Billing cycles');?></div>
                    <table class="clientssummarystats" cellspacing="0" cellpadding="2">
                        <tbody>
                            <?php foreach ($vars['license']->product()->cost as $key => $item):?>
                            <tr>
                                <td width="40%"><?=GoldenSource_translate($key);?></td>
                                <td>
                                    $<?=$vars['license']->product()->priceWithDiscount($key);?> 
                                </td>
                                <td>
                                    <?=number_format($vars['information']->exchangeRateToman*$vars['license']->product()->priceWithDiscount($key));?> <?=GoldenSource_translate('Toman');?>
                                </td>
                            </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                </div>
                <div class="clientssummarybox">
                    <div class="title"><?=GoldenSource_translate('Settings');?></div>
                    <form method="post" action="addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>&licenseId=<?=$vars['license']->id;?>&c=<?=$vars['sessionChecker'];?>">
                        <input type="hidden" name="action" value="changeSettings">
                         <table class="clientssummarystats">
                            <tbody>
                            <tr>
                                <td class="fieldlabel" style="font-size: 13px"><?=GoldenSource_translate('status');?></td>
                                <td class="fieldarea">
                                    <select name="setStatus" class="form-control">
                                        <option value="active" <?=($vars['license']->status == 'active') ? 'selected' : '';?>><?=GoldenSource_translate('Active');?></option>
                                        <option value="suspended" <?=($vars['license']->status == 'suspended') ? 'selected' : '';?>><?=GoldenSource_translate('Suspended');?></option>
                                        <option value="expired" <?=($vars['license']->status == 'expired') ? 'selected' : '';?>><?=GoldenSource_translate('Expired');?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td class="fieldlabel" style="font-size: 13px"><?=GoldenSource_translate('Billing cycle');?></td>
                                <td class="fieldarea">
                                    <select name="setBillingCycle" class="form-control">
                                        <option value="monthly" <?=($vars['license']->cycle == 'monthly') ? 'selected' : '';?>><?=GoldenSource_translate('monthly');?></option>
                                        <option value="quarterly" <?=($vars['license']->cycle == 'quarterly') ? 'selected' : '';?>><?=GoldenSource_translate('quarterly');?></option>
                                        <option value="semiannually" <?=($vars['license']->cycle == 'semiannually') ? 'selected' : '';?>><?=GoldenSource_translate('semi-annually');?></option>
                                        <option value="annually" <?=($vars['license']->cycle == 'annually') ? 'selected' : '';?>><?=GoldenSource_translate('annually');?></option>
                                    </select>
                                </td>
                            </tr>
                    </tbody>
                    </table>
                    <div class="btn-container">
                        <input type="submit" value="<?=GoldenSource_translate('Save');?>" class="btn btn-primary">
                    </div>
                    </form>
                </div>
            </div>
            
            <div class="col-lg-3 col-sm-6">
                 <div class="clientssummarybox">
                    <div class="title"><?=GoldenSource_translate('Change IP');?> (<?=$vars['license']->changeIP;?>/3)</div>
                    <form method="post" action="addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>&licenseId=<?=$vars['license']->id;?>&c=<?=$vars['sessionChecker'];?>">
                        <input type="hidden" name="action" value="changeIP">
                        <div style="font-size: 11px; color: #f48042; font-weight: bold;">
                            <?=GoldenSource_translate('admin_change_ip_note_warning');?>
                        </div>
                        <br>
                        <div align="center">
                            <input name="newIP" class="form-control bottom-margin-10" placeholder="<?=GoldenSource_translate('New IP address');?>">
                            <input type="submit" value="<?=GoldenSource_translate(($vars['license']->changeIP < 3) ? 'ChangeIP' : 'ChangeIPWith2$');?>" class="button btn btn-default">
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-3 col-sm-6">
                <div class="clientssummarybox">
                    <div class="title"><?=GoldenSource_translate('Notes');?></div>
                    <form method="post" action="addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>&licenseId=<?=$vars['license']->id;?>&c=<?=$vars['sessionChecker'];?>">
                        <input type="hidden" name="action" value="updateNote">
                        <div align="center">
                            <textarea name="notes" rows="4" class="form-control bottom-margin-5"><?=$vars['license']->notes;?></textarea>
                            <input type="submit" value="<?=GoldenSource_translate('Save');?>" class="button btn btn-default">
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6 col-sm-12">
                 <div class="clientssummarybox">
                    <div class="title"><?=GoldenSource_translate('ChangeIPLogs');?></div>
                    <table class="clientssummarystats license">
                        <tr>
                            <td class="text-center" style="font-weight: bold"><?=GoldenSource_translate('Old IP address');?></td>
                            <td class="text-center" style="font-weight: bold"><?=GoldenSource_translate('New IP address');?></td>
                            <td class="text-center" style="font-weight: bold"><?=GoldenSource_translate('Date');?></td>
                        </tr>
                        <?php foreach($vars['license']->changeIP_logs as $log):?>
                        <tr>
                            <td class="text-center"><?=$log['from'];?></td>
                            <td class="text-center"><?=$log['to'];?></td>
                            <td class="text-center"><?=$log['date'];?></td>
                        </tr>
                        <?php endforeach;?>
                        <?php if(!sizeof($vars['license']->changeIP_logs)):?>
                        <tr>
                            <td class="text-center" colspan="3"><?=GoldenSource_translate('No records were found');?></td>
                        </tr>
                        <?php endif;?>
                    </table>
                </div>
            </div>
        </div>
        <hr />
        <h1><?=GoldenSource_translate('Installation');?></h1>
        <br>
        <div style="direction: ltr; text-align: left">
        <?php foreach($vars['installationHelp'] as $item):?>
            <h4><?=$item->os;?></h4>
            <div class="message markdown-content">
            <pre><code><?=$item->commands;?></code></pre>
            </div>
            <br />
        <?php endforeach;?>
        </div>
  </div>
</div>
<?php require(__DIR__ . "/copyright.php"); ?>