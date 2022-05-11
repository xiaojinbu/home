@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../wechatpay/wechatpay-guzzle-middleware/tool/CertificateDownloader.php
php "%BIN_TARGET%" %*
