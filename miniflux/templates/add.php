<div class="page-header">
    <h2>New subscription</h2>
    <ul>
        <li><a href="?action=feeds">feeds</a></li>
        <li><a href="?action=import">import</a></li>
        <li><a href="?action=export">export</a></li>
    </ul>
</div>

<form method="post" action="?action=add">
    <label for="url">Site or Feed URL</label>
    <input type="text" name="url" id="url" placeholder="http://website/" autofocus required/>
    <div class="form-actions">
        <button type="submit" class="btn btn-blue">Add</button>
    </div>
</form>