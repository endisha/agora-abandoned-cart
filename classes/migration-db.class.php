<?php 
namespace AgoraAbandonedCart\Classes;

class MigrationDBClass
{

	/**
	 * __construct
	 */
	public function __construct(){
		$this->execute();
	}

	/**
	 * execute
	 * @return void
	 */
	public function execute(){
		register_activation_hook( dirname(__DIR__) . '/agora-abandoned-cart.php', [$this, 'createSchema'] );
    }

	/**
     * Create DB tables
     * @return void
     */
	public function createSchema(){
	    global $table_prefix, $wpdb;
		$schemas = [];
		$sqlSchema = '';

		$dir = glob (dirname(__DIR__) . '/db/'."*.sql"); 
		foreach ($dir as $filename){
			$schemas[basename($filename, ".sql")]  = file_get_contents($filename);
		}
		foreach ($schemas as $filename => $sql) {
			if($wpdb->get_var( "show tables like '{$table_prefix}{$filename}'" ) != $table_prefix.$filename){
				$sqlSchema .= $sql;
			}
		}
		if(!empty($sqlSchema)){
			$sqlSchema = str_replace('{prefix}', $table_prefix, $sqlSchema);
			require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
			dbDelta($sqlSchema);
		}
	}
	
}
new MigrationDBClass;