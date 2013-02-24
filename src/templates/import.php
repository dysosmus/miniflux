<div class="page-header">
    <h2>OPML Import</h2>
    <ul>
        <li><a href="?action=feeds">feeds</a></li>
        <li><a href="?action=add">add</a></li>
        <li><a href="?action=export">export</a></li>
    </ul>
</div>

<form method="post" action="?action=import" enctype="multipart/form-data">
    <label for="file">OPML file</label>
    <input type="file" name="file" required/>
    <div class="form-actions">
        <button type="submit" class="btn btn-blue">Import</button>
    </div>
</form>