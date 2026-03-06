    </div><!-- /.layout -->
    <script src="assets/js/toast.js"></script>
    <script src="assets/js/app.js"></script>
    <?php if (!empty($extraJs)): ?>
    <script><?= $extraJs ?></script>
    <?php endif; ?>
    <?php if (!empty($extraScripts)): foreach ($extraScripts as $src): ?>
    <script src="<?= $src ?>"></script>
    <?php endforeach; endif; ?>
</body>
</html>
