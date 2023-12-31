<?php
/**
 * Plugin Name: Member Area Report
 * Plugin URI: https://github.com/drajathasan/slims-memberarea_report
 * Description: Download report di member area sebagai PDF seperti sejarah peminjaman, dan peminjaman terkini
 * Version: 1.0.0
 * Author: Drajat Hasan
 * Author URI: https://t.me/drajathasan
 */
use SLiMS\Plugins;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/SlimsPdf.php';

Plugins::getInstance()->registerMenu('opac', 'download_current_loan', __DIR__ . '/pages/download_current_loan.inc.php');
Plugins::getInstance()->registerMenu('opac', 'download_loan_history', __DIR__ . '/pages/download_loan_history.inc.php');