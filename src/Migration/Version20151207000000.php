<?php
namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

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
	
	private function dropOmiseConfigTable(Schema $schema) {
		$schema->dropTable('plg_omise_config');
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
		if(!$table->hasColumn('info')) $table->addColumn('info', 'text', array('notnull' => false));
		if(!$table->hasColumn('delete_flg'))$table->addColumn('delete_flg', 'tinyint', array('notnull' => true, 'default' => 0));
		if(!$table->hasColumn('create_date'))$table->addColumn('create_date', 'datetime', array('notnull' => true));
		if(!$table->hasColumn('update_date'))$table->addColumn('update_date', 'datetime', array('notnull' => true));
		
		if($isCreateedTable) $table->setPrimaryKey(array('id'));
	}
}
