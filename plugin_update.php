<?php
class plugin_update
{
    /**
     * @param  array               $plugin_info プラグイン情報
     * @param  SC_Plugin_Installer $installer   プラグインインストーラー
     *
     * @return void
     */
    public static function update(array $plugin_info, SC_Plugin_Installer $installer)
    {
        copy(
            DOWNLOADS_TEMP_PLUGIN_UPDATE_DIR . "class/models/Omise_Models_Charge.php",
            PLUGIN_UPLOAD_REALDIR . $plugin_info['plugin_code'] . "/class/models/Omise_Models_Charge.php"
        );

        copy(
            DOWNLOADS_TEMP_PLUGIN_UPDATE_DIR . "inc/include.php",
            PLUGIN_UPLOAD_REALDIR . $plugin_info['plugin_code'] . "/inc/include.php"
        );

        copy(
            DOWNLOADS_TEMP_PLUGIN_UPDATE_DIR . "plugin_info.php",
            PLUGIN_UPLOAD_REALDIR . $plugin_info['plugin_code'] . "/plugin_info.php"
        );

        copy(
            DOWNLOADS_TEMP_PLUGIN_UPDATE_DIR . "plugin_update.php",
            PLUGIN_UPLOAD_REALDIR . $plugin_info['plugin_code'] . "/plugin_update.php"
        );

        copy(
            DOWNLOADS_TEMP_PLUGIN_UPDATE_DIR . "logo.png",
            PLUGIN_UPLOAD_REALDIR . $plugin_info['plugin_code'] . "/logo.png"
        );

        $installer->copyFile('logo.png', 'logo.png');
    }
}
