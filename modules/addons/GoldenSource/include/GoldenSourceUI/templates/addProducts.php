<style>
    input, select, textarea {
        font-family: Tahoma;font-size: 11px;
    }
    .GoldenSource .head {
        padding: 10px 25px 10px 25px;
        background-color: #666;
        font-weight: bold;
        font-size: 14px;
        color: #E3F0FD;
        margin: 0 0 15px 0;
        -moz-border-radius: 5px;
        -webkit-border-radius: 5px;
        -o-border-radius: 5px;
        border-radius: 5px;
    }
</style>
<div class="GoldenSource">
    <div class="head"><?=GoldenSource_translate('Fast installation');?></div>
    <div class="infobox">
        <strong><span class="title"><?=GoldenSource_translate('Fast installation');?></span></strong>
        <br />
        <?=GoldenSource_translate('fast_install_desc');?>
    </div>
    <form method="post" action="addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>&addProducts">
                <table class="form" width="100%">
                <tbody>
                <tr>
                    <td class="fieldlabel" style="font-size: 13px"><?=GoldenSource_translate('Type');?></td>
                    <td class="fieldarea">
                        <select name="productType" class="form-control">
                             <option value="addon" <?=($vars['productType'] == 'addon') ? 'selected' : '';?>><?=GoldenSource_translate('Addon');?></option>
                             <option value="product" <?=($vars['productType'] == 'product') ? 'selected' : '';?>><?=GoldenSource_translate('Product');?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="fieldlabel" style="font-size: 13px"><?=GoldenSource_translate('Product Group');?></td>
                    <td class="fieldarea">
                        <select name="productGroup" class="form-control">
                            <?php foreach($vars['productGroups'] as $key => $item):?>
                                <option value="<?=$item->id;?>" <?=($vars['productGroup'] == $item->id) ? 'selected' : '';?>>(<?=$item->id;?>) <?=$item->name;?></option>
                            <?php endforeach;?>
                        </select>
                        <span><?=GoldenSource_translate('Leave empty if you are adding addon');?></span>
                    </td>
                </tr>
                <tr>
                    <td class="fieldlabel" style="font-size: 13px"><?=GoldenSource_translate('CSP Product');?></td>
                    <td class="fieldarea">
                        <select id="multi-view" name="product[]" class="form-control selectize-multi-select" multiple="multiple" data-value-field="id">
                             <?php foreach($vars['products'] as $key => $item):?>
                                <option value="<?=$item->id;?>" <?=(in_array($item->id, $vars['product'])) ? 'selected' : '';?>><?=$item->fullName;?></option>
                            <?php endforeach;?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="fieldlabel" style="font-size: 13px"><?=GoldenSource_translate('CSP Product');?></td>
                    <td class="fieldarea">
                        <select name="allowChangeIP" class="form-control">
                             <option value="1" <?=($vars['allowChangeIP']) ? 'selected' : '';?>><?=GoldenSource_translate('Yes');?></option>
                             <option value="0" <?=(!$vars['allowChangeIP']) ? 'selected' : '';?>><?=GoldenSource_translate('No');?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="fieldlabel" style="font-size: 13px"><?=GoldenSource_translate('Currency');?></td>
                    <td class="fieldarea">
                        <select name="currency" class="form-control">
                            <?php foreach($vars['currencies'] as $key => $item):?>
                                <option value="<?=$item->id;?>" <?=($vars['currency'] == $item->id) ? 'selected' : '';?>><?=$item->code;?></option>
                            <?php endforeach;?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="fieldlabel" style="font-size: 13px"><?=GoldenSource_translate('Exchange rate');?></td>
                    <td class="fieldarea">
                        <input type="number" name="exchangeRate" class="form-control" value="<?=$vars['exchangeRate'];?>">
                    </td>
                </tr>
                <tr>
                    <td class="fieldlabel" style="font-size: 13px"><?=GoldenSource_translate('Round by');?></td>
                    <td class="fieldarea">
                        <input type="number" name="roundBy" class="form-control" value="<?=$vars['roundBy'];?>">
                    </td>
                </tr>
                <tr>
                    <td class="fieldlabel" style="font-size: 13px"></td>
                    <td class="fieldarea">
                        <input type="checkbox" name="updateExisting" <?=$vars['updateExisting'] ? 'checked' : '';?> value="1">
                        <?=GoldenSource_translate('Update existing product/addon instead of adding new ones');?>
                    </td>
                </tr>
                <tr>
                    <td class="fieldlabel" style="font-size: 13px"></td>
                    <td class="fieldarea">
                        <input type="checkbox" name="updateAddonPackages" <?=$vars['updateAddonPackages'] ? 'checked' : '';?> value="1">
                        <?=GoldenSource_translate('Update packages for addons (if addon)');?>
                    </td>
                </tr>
                <tr>
                    <td class="fieldlabel" style="font-size: 13px"></td>
                    <td class="fieldarea">
                        <input type="checkbox" name="updateServicesPrices" <?=$vars['updateServicesPrices'] ? 'checked' : '';?> value="1">
                        <?=GoldenSource_translate('Update services prices');?>
                    </td>
                </tr>
            </tbody>
            </table>
            <div class="btn-container">
                <input type="submit" value="<?=GoldenSource_translate('Add');?>" class="btn btn-primary">
            </div>
        </form>
        <?php if(isset($vars['success']) && !empty($vars['success'])):?>
            <div class="successbox">
                <strong><span class="title"><?=GoldenSource_translate('Changes Saved Successfully!');?></span></strong>
                <br>
                <?=$vars['success'];?>
            </div>
        <?php endif;?>
        <?php if(isset($vars['error']) && !empty($vars['error'])):?>
            <div class="errorbox"><strong><span class="title"><?=GoldenSource_translate('Error');?></span></strong><br><?=$vars['error'];?></div>
        <?php endif;?>
</div>