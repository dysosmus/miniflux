<div class="page-header">
    <h2>New subscription</h2>
    <?php include __DIR__.'/feed_menu.php' ?>
</div>

<form method="post" action="?action=add">
    <label for="url">Site or Feed URL</label>
    <input type="text" name="url" id="url" placeholder="http://website/" autofocus required/>
    <div class="form-actions">
        <button type="submit" class="btn btn-blue">Add</button>
    </div>
</form>