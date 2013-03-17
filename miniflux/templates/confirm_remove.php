<div class="page-header">
    <h2>Confirmation</h2>
</div>

<p class="alert alert-info">Do you really want to remove this subscription: "<?= Helper\escape($feed['title']) ?>"?</p>

<div class="form-actions">
    <a href="?action=remove&amp;feed_id=<?= $feed['id'] ?>" class="btn btn-red">Yes</a>
    or <a href="?action=feeds">cancel</a>
</div>