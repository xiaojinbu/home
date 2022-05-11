<div class="wrapper">
    <div style="margin-bottom: 0;" class="alert <?php echo $className; ?> alert-dismissable">
        <?php if($closeButton) { ?>
        <button aria-hidden="true" data-dismiss="alert" class="close" type="button">×</button>
        <?php } ?>
        <?php echo $message; ?>
    </div>
</div>