<?php

/**
 * Companies_Model_Base_CompanyArticleComment
 * 
 * This class has been auto-generated by the Doctrine ORM Framework
 * 
 * @property integer $id
 * @property integer $article_id
 * @property string $email
 * @property string $name
 * @property clob $comment
 * @property integer $published
 * @property Companies_Model_CompanyArticle $Article
 * 
 * @package    ##PACKAGE##
 * @subpackage ##SUBPACKAGE##
 * @author     ##NAME## <##EMAIL##>
 * @version    SVN: $Id: Builder.php 7490 2010-03-29 19:53:27Z jwage $
 */
abstract class Companies_Model_Base_CompanyArticleComment extends Doctrine_Record
{
    public function setTableDefinition()
    {
        $this->setTableName('company_article_comments');
        $this->hasColumn('id', 'integer', 4, array(
             'type' => 'integer',
             'primary' => true,
             'unsigned' => true,
             'autoincrement' => true,
             'length' => '4',
             ));
        $this->hasColumn('article_id', 'integer', 4, array(
             'type' => 'integer',
             'unsigned' => true,
             'notnull' => true,
             'length' => '4',
             ));
        $this->hasColumn('email', 'string', 1000, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '1000',
             ));
        $this->hasColumn('name', 'string', 1000, array(
             'type' => 'string',
             'notnull' => true,
             'length' => '1000',
             ));
        $this->hasColumn('comment', 'clob', 2147483647, array(
             'type' => 'clob',
             'notnull' => true,
             'length' => '2147483647',
             ));
        $this->hasColumn('published', 'integer', 1, array(
             'type' => 'integer',
             'unsigned' => true,
             'notnull' => true,
             'default' => 0,
             'length' => '1',
             ));

        $this->option('type', 'INNODB');
        $this->option('collate', 'utf8_unicode_ci');
        $this->option('charset', 'utf8');
    }

    public function setUp()
    {
        parent::setUp();
        $this->hasOne('Companies_Model_CompanyArticle as Article', array(
             'local' => 'article_id',
             'foreign' => 'id',
             'onDelete' => 'cascade',
             'onUpdate' => 'cascade'));

        $timestampable0 = new Doctrine_Template_Timestampable();
        $this->actAs($timestampable0);
    }
}