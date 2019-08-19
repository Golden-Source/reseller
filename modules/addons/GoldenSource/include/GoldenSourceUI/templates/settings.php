<p><?=GoldenSource_translate("Settings desc");?></p>
<form action="addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>&do=saveSettings" method="POST">
    <table class="datatable">
        <tr>
            <td><?=GoldenSource_translate("Change IP Price");?></td>
            <td><input name="change_ip_price" type="number" value="<?=$vars['settings']->change_ip_price;?>" step="0.0001" class="form-control"></td>
        </tr>
        <tr>
            <td colspan="2" class="text-center">
                <button type="submit" class="btn btn-primary"><?=GoldenSource_translate("Save");?></button>
            </td>
        </tr>
    </table>
</form>