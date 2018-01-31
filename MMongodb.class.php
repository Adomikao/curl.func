
<?php 


/**
 * Mongodb类
 * examples:
 * $mongo = new MMongodb("127.0.0.1:11223");
 * $mongo->selectDb("test_db");
 * 创建索引
 * $mongo->index("test_table", array("id"=>1), array('unique'=>true));
 * 获取表的记录
 * $mongo->count("test_table");
 * 插入记录
 * $mongo->insert("test_table", array("id"=>2, "title"=>"asdqw"));
 * 更新记录
 * $mongo->update("test_table", array("id"=>1),array("id"=>1,"title"=>"bbb"));
 * 更新记录-存在时更新，不存在时添加-相当于set
 * $mongo->update("test_table", array("id"=>1),array("id"=>1,"title"=>"bbb"),array("upsert"=>1));
 * 查找记录
 * $mongo->find("c", array("title"=>"asdqw"), array("start"=>2,"limit"=>2,"sort"=>array("id"=>1)))
 * 查找一条记录
 * $mongo->findOne("$mongo->findOne("ttt", array("id"=>1))", array("id"=>1));
 * 删除记录
 * $mongo->delete("ttt", array("title"=>"bbb"));
 * 仅删除一条记录
 * $mongo->delete("ttt", array("title"=>"bbb"), array("justOne"=>1));
 * 获取Mongo操作的错误信息
 * $mongo->getError();
 */

class MMongodb {

    //Mongodb连接
    var $mongo;

    var $curr_db_name;
    var $curr_table_name;
    var $error;

    /**
     * 构造函数
     * 支持传入多个mongo_server(1.一个出问题时连接其它的server 2.自动将查询均匀分发到不同server)
     *
     * 参数：
     * $mongo_server:数组或字符串-array("127.0.0.1:1111", "127.0.0.1:2222")-"127.0.0.1:1111"
     * $connect:初始化mongo对象时是否连接，默认连接
     * $auto_balance:是否自动做负载均衡，默认是
     *
     * 返回值：
     * 成功：mongo object
     * 失败：false
     */
    function __construct($connect=true, $auto_balance=true)
    {
        $mongo_server = $GLOBALS['mongo']['server'];
        $persist = $GLOBALS['mongo']['persist'];
        $db = $GLOBALS['mongo']['db'];
        if (is_array($mongo_server))
        {
            $mongo_server_num = count($mongo_server);
            if ($mongo_server_num > 1 && $auto_balance)
            {
                $prior_server_num = rand(1, $mongo_server_num);
                $rand_keys = array_rand($mongo_server,$mongo_server_num);
                $mongo_server_str = $mongo_server[$prior_server_num-1];
                foreach ($rand_keys as $key)
                {
                    if($key != $prior_server_num - 1)
                    {
                        $mongo_server_str .= ',' . $mongo_server[$key];
                    }
                }
            }
            else
            {
                $mongo_server_str = implode(',', $mongo_server);
            }
        }
        else
        {
            $mongo_server_str = $mongo_server;
        }
        try {
            $this->mongo = new Mongo($mongo_server_str, array('connect'=>$connect, 'socketTimeoutMS'=>60000));
            $this->selectDb($db);
            MongoCursor::$timeout = 60000;
        }
        catch (MongoConnectionException $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
    }

    function getInstance($flag=array())
    {
        static $mongodb_arr;
        if (empty($flag['tag']))
        {
            $flag['tag'] = 'default';          }
        if (isset($flag['force']) && $flag['force'] == true)
        {
            $mongo = new MMongodb();
            if (empty($mongodb_arr[$flag['tag']]))
            {
                $mongodb_arr[$flag['tag']] = $mongo;
            }
            return $mongo;
        }
        else if (isset($mongodb_arr[$flag['tag']]) && is_resource($mongodb_arr[$flag['tag']]))
        {
            return $mongodb_arr[$flag['tag']];
        }
        else
        {
            $mongo = new MMongodb();
            $mongodb_arr[$flag['tag']] = $mongo;
            return $mongo;
        }
    }

    /**
     * 连接mongodb server
     *
     * 参数：无
     *
     * 返回值：
     * 成功：true
     * 失败：false
     */
    function connect()
    {
        try {
            $this->mongo->connect();
            return true;
        }
        catch (MongoConnectionException $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * select db
     *
     * 参数：$dbname
     *
     * 返回值：无
     */
    function selectDb($dbname)
    {
        $this->curr_db_name = $dbname;
    }

    /**
     * 创建索引：如索引已存在，则返回。
     *
     * 参数：
     * $table_name:表名
     * $index:索引-array("id"=>1)-在id字段建立升序索引
     * $index_param:其它条件-是否唯一索引等 background unique
     *
     * 返回值：
     * 成功：true
     * 失败：false
     */
    function index($table_name, $index, $index_param=array())
    {
        $dbname = $this->curr_db_name;
        $index_param['safe'] = 1;
        try {
            $this->mongo->$dbname->$table_name->ensureIndex($index, $index_param);
            return true;
        }
        catch (MongoCursorException $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * 删除索引
     * 
     */
    function dropIndex($table_name, $index)
    {
        $dbname = $this->curr_db_name;        
        try {
            $this->mongo->$dbname->$table_name->deleteIndex($index);
            return true;
        }
        catch (MongoCursorException $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 插入记录
     *
     * 参数：
     * $table_name:表名
     * $record:记录
     *
     * 返回值：
     * 成功：true
     * 失败：false
     */
    function insert($table_name, $record)
    {
        $dbname = $this->curr_db_name;
        try {
            $this->mongo->$dbname->$table_name->insert($record, array('safe'=>true,'timeout'=>60000));
            return true;
        }
        catch (MongoCursorException $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
    }
