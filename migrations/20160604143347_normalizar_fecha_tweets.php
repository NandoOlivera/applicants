<?php

use Phinx\Migration\AbstractMigration;

class NormalizarFechaTweets extends AbstractMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-change-method
     *
     * Uncomment this method if you would like to use it.
     *
    public function change()
    {
    }
    */
    
    /**
     * Migrate Up.
     */
    public function up()
    {
        
        $this->query("UPDATE twitter_tweets SET twitter_created_at = CASE WHEN CHAR_LENGTH(twitter_created_at) = 30 THEN STR_TO_DATE(STR_TO_DATE(twitter_created_at, '%a %b %d %H:%i:%s +0000 %Y'),'%Y-%m-%d %H:%i:%s') ELSE DATE_FORMAT(twitter_created_at,'%Y-%m-%d %H:%i:%s') END;");
        $this->query('ALTER TABLE twitter_tweets MODIFY twitter_created_at DATETIME;');
        
    }

    /**
     * Migrate Down.
     */
    public function down()
    {

    }
}