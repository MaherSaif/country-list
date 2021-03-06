<?php

/**
 * This file is part of the Country List project.
 *
 *  (c) Саша Стаменковић <umpirsky@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Umpirsky\Country\Dumper;

use Umpirsky\Country\Dumper\Dumper;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Table;
use Zend\Db\Adapter\Platform;
use Zend\Db\Sql\Insert;

/**
 * SQL dumper.
 *
 * @author Саша Стаменковић <umpirsky@gmail.com>
 */
abstract class SqlDumper extends Dumper {

    /**
     * {@inheritdoc}
     */
    public function dump(array $data) {

        $table = new Table('country', array(), array(), array(), false, array());
        $table->addColumn('id', 'string', array('length' => 2, 'notnull' => true));
        $table->setPrimaryKey(array('id'));
        $table->addColumn('name', 'string', array('length' => 64));

        $insertSql = '';
        $insert = new Insert('country');
        foreach ($data as $id => $name) {
            $insertSql .= $insert
                ->values(array('id' => $id, 'name' => $name))
                ->getSqlString($this->getPlatform()) . ';' . PHP_EOL;
        }

        return sprintf(
            '%s;%s%s%s',
            array_pop(DriverManager::getConnection(array('driver' => $this->getDriver()))
                ->getDatabasePlatform()
                ->getCreateTableSQL($table, AbstractPlatform::CREATE_INDEXES)),
            PHP_EOL,
            PHP_EOL,
            $insertSql
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormat() {

        return sprintf('%s.sql', parent::getFormat());
    }

    /**
     * Gets SQL driver.
     *
     * @return string
     */
    public abstract function getDriver();

    /**
     * Gets platform.
     *
     * @return \Zend\Db\Adapter\Platform\PlatformInterface
     */
    public function getPlatform() {

        switch ($this->getDriver()) {
            case 'pdo_mysql':

                return new Platform\Mysql();
                break;

            case 'pdo_sqlite':

                return new Platform\Sqlite();
                break;

            case 'pdo_sqlsrv':

                return new Platform\SqlServer();
                break;

            default:

                throw new \Exception(sprintf('Unknown platform %s.', $this->getPlatform()));
                break;
        }
    }
}
