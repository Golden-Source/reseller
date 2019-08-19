<a href="addonmodules.php?module=GoldenSource&serverId=<?=$vars['serverId'];?>">« <?=GoldenSource_translate('Back to licenses');?></a>
<br>
<br>
<h1><?=GoldenSource_translate('Installation');?> (<?=$vars['fullName'];?>)</h1>
<div style="direction: ltr">
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