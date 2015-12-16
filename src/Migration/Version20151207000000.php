<?php
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\Yaml\Yaml;

class Version20151207000000 extends AbstractMigration {
	/**
	 * データベース変更処理
	 */
	public function up(Schema $schema) {
		$this->createOmiseConfigTable($schema);
	}
	
	public function down(Schema $schema) {
		$this->dropOmiseConfigTable($schema);
	}
	
    public function postUp(Schema $schema) {
        $this->insertOmiseConfig();
    }
	
	private function dropOmiseConfigTable(Schema $schema) {
		$tableName = 'plg_omise_config';
		
		if($schema->hasTable($tableName)) {
			$schema->dropTable($tableName);
		}
	}
	
	/**
	 * OmiseConfigテーブルの作成
	 * @param Schema $schema
	 */
	private function createOmiseConfigTable(Schema $schema) {
		$tableName = 'plg_omise_config';
		
		if(!$schema->hasTable($tableName)) {
			$table = $schema->createTable($tableName);
			$isCreateedTable = true;
		} else {
			$table = $schema->getTable($tableName);
			$isCreateedTable = false;
		}
		
		if(!$table->hasColumn('id')) $table->addColumn('id', 'integer', array('autoincrement' => true));
		if(!$table->hasColumn('code')) $table->addColumn('code', 'text', array('notnull' => true));
        if(!$table->hasColumn('payment_id')) $table->addColumn('payment_id', 'integer', array('notnull' => false));
		if(!$table->hasColumn('info')) $table->addColumn('info', 'text', array('notnull' => true));
		if(!$table->hasColumn('delete_flg'))$table->addColumn('delete_flg', 'smallint', array('notnull' => true, 'default' => 0));
		if(!$table->hasColumn('create_date'))$table->addColumn('create_date', 'datetime', array('notnull' => true));
		if(!$table->hasColumn('update_date'))$table->addColumn('update_date', 'datetime', array('notnull' => true));
		
		if($isCreateedTable) $table->setPrimaryKey(array('id'));
	}
	
	private function insertOmiseConfig() {
		$tableName = 'plg_omise_config';
		
        $code = 'OmisePaymentGateway';
        $createDate = date('Y-m-d H:i:s');
        
        $insert = "INSERT INTO $tableName (code, payment_id, info, create_date, update_date)"
        		." VALUES ('$code', NULL, '', '$createDate', '$createDate');";
        $this->connection->executeUpdate($insert);
	}
}
