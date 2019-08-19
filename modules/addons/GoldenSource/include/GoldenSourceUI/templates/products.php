<div class="tablebg">
    <h4><?=GoldenSource_translate('Products');?> (<?=sizeof($vars['products']);?>)</h4>
    <table style="text-align: center;" id="sortabletbl1" class="datatable licenses" width="100%">
        <tbody>
        <tr>
            <th style="width: 30px">#</th>
            <th><?=GoldenSource_translate('Name');?></th>
            <th style="max-width: 100px"><?=GoldenSource_translate('setupfee');?></th>
            <th style="max-width: 100px"><?=GoldenSource_translate('monthly');?></th>
            <th style="max-width: 100px"><?=GoldenSource_translate('quarterly');?></th>
            <th style="max-width: 100px"><?=GoldenSource_translate('semi-annually');?></th>
            <th style="max-width: 100px"><?=GoldenSource_translate('annually');?></th>
        </tr>
        <?php foreach($vars['products'] as $product):?>
            <tr>
                <td><?=$product->id;?></td>
                <td style="white-space: initial"><?=$product->fullName;?></td>
                <td style="direction: ltr">$<?=$product->priceWithDiscount('setupfee');?> <br /> (<?=round($vars['exchangeRateToman']*$product->priceWithDiscount('setupfee'), 4);?> IRT)</td>
                <td style="direction: ltr">$<?=$product->priceWithDiscount('monthly');?> <br /> (<?=round($vars['exchangeRateToman']*$product->priceWithDiscount('monthly'), 4);?> IRT)</td>
                <td style="direction: ltr">$<?=$product->priceWithDiscount('quarterly');?> <br /> (<?=round($vars['exchangeRateToman']*$product->priceWithDiscount('quarterly'), 4);?> IRT)</td>
                <td style="direction: ltr">$<?=$product->priceWithDiscount('semiannually');?> <br /> (<?=round($vars['exchangeRateToman']*$product->priceWithDiscount('semiannually'), 4);?> IRT)</td>
                <td style="direction: ltr">$<?=$product->priceWithDiscount('annually');?> <br /> (<?=round($vars['exchangeRateToman']*$product->priceWithDiscount('annually'), 4);?> IRT)</td>
            </tr>
        <?php endforeach;?>
        </tbody>
    </table>
</div>