<?php !defined('BASEPATH') && exit('No direct script access allowed');
/**
 * NodePrint
 *
 * 基于HTML5及CSS3的轻论坛程序
 * 
 * NodePrint is an open source BBS System built on PHP and MySQL.
 *
 * @package	NodePrint
 * @author		airyland <i@mao.li>
 * @copyright	Copyright (c) 2012, mao.li.
 * @license		MIT
 * @link		https://github.com/airyland/nodeprint
 * @version	0.0.5
 */
 
// ------------------------------------------------------------------------

/**
 * STBLOG Metas Model Class
 *
 * Ԫ��ݲ���Model
 *
 * @package		STBLOG
 * @subpackage	Models
 * @category	Models
 * @author		Saturn <huyanggang@gmail.com>
 * @link		http://code.google.com/p/stblog/
 */
class Metas_mdl extends CI_Model {

	const TBL_METAS = 'metas';
	const TBL_RELATIONSHIPS = 'relationships';
	const TBL_POSTS = 'posts';

	/**
     * �������ͣ�����/��ǩ
     * 
     * @access private
     * @var array
     */
	private $_type = array('category','tag');
	
	/**
     * ����Ԫ���
     * 
     * @access public
     * @var mixed
     */
	public $metas = NULL;
	
	/**
     * ���캯��
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
		parent::__construct();
		log_message('debug', "STBLOG: Metas Model Class Initialized");
    }

	/**
     * ���post id��ȡԪ����б�
     *
     *	�������Ŀ����һ���Զ������µ�����metas��Ȼ��ͨ��$this->metas_mdl->metas['YOUR_KEY']��ȡ��Ӧ��meta������category��
     *	
     * 
     * @access public
	 * @param int $pid ����ID
	 * @param bool $return �Ƿ񷵻�ģʽ
	 * @return array
     */
	public function get_metas($pid = 0, $return = FALSE)
	{
		//���metas����
		$this->metas = NULL;
		
		$metas = array();
		
		//��ȡDB
		if(!empty($pid))
		{
			$this->db->select(self::TBL_METAS.'.*,'.self::TBL_RELATIONSHIPS.'.pid');
			$this->db->join(self::TBL_RELATIONSHIPS,self::TBL_RELATIONSHIPS.'.mid = '.self::TBL_METAS.'.mid AND '.self::TBL_RELATIONSHIPS.'.pid='.intval($pid), 'INNER');
		}
		
		$query = $this->db->get(self::TBL_METAS);
		
		if ($query->num_rows() > 0)
        {
            $metas = $query->result_array();
        }
		
		$query->free_result();
		
		//����Ƿ���ģʽ
		if($return)
		{
			return $metas;
		}
		
		//��ʼ��һ��metas����
		foreach($this->_type as $type)
		{
			$this->metas[$type] = array();
		}
	
		if(!empty($metas))
		{
			//��ݲ�ͬ��metas�����Զ�push���Ӧ������
			foreach($metas as $meta)
			{
				foreach($this->_type as $type)
				{
					if($type == $meta['type'])
					{
						array_push($this->metas[$type], $meta);
					}
				}
			}	
		}
	}

	/**
     * ��ȡ����metas
     * 
     * @access public
     * @param  strint $type ����
     * @return object
     */
	public function list_metas($type = 'category')
	{
		if(in_array($type, $this->_type))
		{
			$this->db->where(self::TBL_METAS.'.type', $type);
		}
		
		return $this->db->get(self::TBL_METAS);
	}

	/**
     * ����metas����
     * 
     * @access public
     * @param  strint $type ����
     * @return int
     */
	public function count_metas($type = 'category')
	{
		if(in_array($type, $this->_type))
		{
			$this->db->where(self::TBL_METAS.'.type', $type);
		}

		return $this->db->count_all_results(self::TBL_METAS);
	}

	/**
     *  ��ȡԪ���
     * 
     *  @access public
	 *	@param string $type Ԫ�����𣺣�"category"|"tag"|"byID"��
	 *	@param string $name Ԫ������
	 *	@return object �� result object
     */
	public function get_meta($type = 'category', $name = '')
	{
		if(empty($name)) exit();
		
		if($type && in_array($type, $this->_type))
		{
			$this->db->where(self::TBL_METAS.'.type',$type);
			$this->db->where(self::TBL_METAS.'.name',$name);
		}
		
		if($type && strtoupper($type) == 'BYID')
		{
			$this->db->where(self::TBL_METAS.'.mid', intval($name));
		}
		
		return $this->db->get(self::TBL_METAS)->row();
	}

	/**
     * ����������ȡmeta��Ϣ
     * 
     * @access public
	 * @param array $meta_data  ����
     * @return object
     */
	public function get_meta_by_slug($slug)
	{
		$this->db->where(self::TBL_METAS.'.slug', $slug);
		
		return $this->db->get(self::TBL_METAS)->row();
	}

	/**
     * ���meta�Ƿ����
     * 
     * @access public
	 * @param string - $type ����
	 * @param string - $key ��λ��
	 * @param string - $value ����
	 * @param int    - $exclude_mid Ҫ�ų��mid
     * @return bool
     */
	public function check_exist($type = 'category', $key = 'name', $value = '', $exclude_mid = 0)
	{
		$this->db->select('mid')->from(self::TBL_METAS)->where($key, trim($value));
		
		if(!empty($exclude_mid) && is_numeric($exclude_mid))
		{
			$this->db->where('mid !=', $exclude_mid);	
		}
		
		if($type && in_array($type, $this->_type))
		{
			$this->db->where('type', $type);
		}
		
		$query = $this->db->get();
		
		$num = $query->num_rows();
		
		$query->free_result();
		
		return ($num > 0) ? TRUE : FALSE;	
	}
	 
	 /**
	 * ������ݵ�ָ������״̬�������meta�ļ�����Ϣ
	 * 
	 * @access public
	 * @param int $mid meta id
	 * @param string $type ���
	 * @param string $status ״̬
	 * @return void
	 */
	public function refresh_count($mid, $type, $status = 'publish')
	{
		//calculation
		$num = $this->db->select(self::TBL_POSTS.'.pid')
					->from(self::TBL_POSTS)
					->join(self::TBL_RELATIONSHIPS, self::TBL_POSTS.'.pid = '.self::TBL_RELATIONSHIPS.'.pid')
        			->where(self::TBL_RELATIONSHIPS.'.mid', $mid)
        			->where(self::TBL_POSTS.'.type', $type)
        			->where(self::TBL_POSTS.'.status', $status)
        			->count_all_results();
		
		//update
		$this->update_meta($mid, array('count' => $num));
	}
	
    /**
     * �ϲ����
     * 
     * @access public
     * @param int $mid �������
     * @param string $type �������
     * @param array $metas ��Ҫ�ϲ�����ݼ�
     * @return void
     */
	public function merge_meta($mid, $type, $metas = array())
	{
		$query = $this->db->select('pid')
        	 		  ->from(self::TBL_RELATIONSHIPS)
        	  		  ->where('mid', $mid)->get();

       	$posts = Common::array_flatten($query->result_array(), 'pid');
        	  		  
       	$query -> free_result();
       	
       	foreach($metas as $meta)
       	{
       		if($mid !== $meta)
       		{
       			$query = $this->db->select('pid')
        	 		  ->from(self::TBL_RELATIONSHIPS)
        	  		  ->where('mid', $meta)->get();
        	 	
        	 	//record posts previously categorized under this special meta
        	  	$exist_posts = Common::array_flatten($query->result_array(),'pid');
        	  	
        	  	$query->free_result();
        	  	
        	  	//delete this special meta
        		$this->db->delete(self::TBL_METAS,
						  array(
						  	'mid'	=>	$meta,
						  	'type'	=>	$type
						 ));
				
				//only get the diff posts that we need to operate on.
				$diff_posts = array_diff($exist_posts, $posts);
				
				//delete the relationship
				$this->remove_relationship('mid',$meta);
				
				//add new relationship
				foreach($diff_posts as $diff_post)
				{
					$this->add_relationship(array('mid'=> $mid,'pid'=> $diff_post));
				}
				
				unset($exist_posts);
       		}
       	}
       	
       	//get new count
       	$num = $this->db->select(self::TBL_RELATIONSHIPS.'.mid')
					->from(self::TBL_RELATIONSHIPS)
        			->where(self::TBL_RELATIONSHIPS.'.mid', $mid)
        			->count_all_results();
       	
       	//update new count
       	$this->update_meta($mid, array('count' => $num));
	}

    /**
     * meta�����Լ�һ
     * 
     * @access public
     * @param int $mid meta id
     * @return void
     */
	public function meta_num_minus($mid)
	{
		$this->db->query('UPDATE '.self::TBL_METAS.' SET `count` = `count`-1 WHERE `mid`='.$mid.'');
	}

    /**
     * meta��������һ
     * 
     * @access public
     * @param int $mid meta id
     * @return void
     */
	public function meta_num_plus($mid)
	{
		$this->db->query('UPDATE '.self::TBL_METAS.' SET `count` = `count`+1 WHERE `mid`='.$mid.'');
	}
	
    /**
     * ���tag��ȡID
     * 
     * @access public
     * @param  mixed $inputTags ��ǩ��
     * @return mixed
     */
    public function scan_tags($inputTags)
    {
        $tags = is_array($inputTags) ? $inputTags : array($inputTags);
        $result = array();
        
        foreach ($tags as $tag) 
        {
            if (empty($tag)) 
            {
                continue;
            }
        
        	$row = $this->db->select('*')
        					->from(self::TBL_METAS)
        					->where('type','tag')
        					->where('name',$tag)
        					->limit(1)
        					->get()
        					->row();
            
            if ($row) 
            {
                $result[] = $row->mid;
            } 
            else 
            {
                $slug = Common::repair_slugName($tag);
                
                if ($slug) 
                {
                    $result[] = $this->add_meta(array(
			                        'name'  =>  $tag,
			                        'slug'  =>  $slug,
			                        'type'  =>  'tag',
			                        'count' =>  0,
			                        'order' =>  0,
			                    ));
                }
            }
        }
        
        return is_array($inputTags) ? $result : current($result);
    }
    
// -----------------------CRUD---------------------------------------------
	/**
     * ���meta
     * 
     * @access public
	 * @param  array $meta_data  ����
     * @return boolean �ɹ����
     */
	public function add_meta($meta_data)
	{
		$this->db->insert(self::TBL_METAS, $meta_data);
		
		return ($this->db->affected_rows() ==1) ? $this->db->insert_id() : FALSE;
	}

	/**
     * ���Ԫ���/���ݹ�ϵ
     * 
     * @access public
	 * @param  array $relation_data  ����
     * @return boolean �ɹ����
     */
	public function add_relationship($relation_data)
	{
		$this->db->insert(self::TBL_RELATIONSHIPS, $relation_data);
		
		return ($this->db->affected_rows()==1) ? $this->db->insert_id() : FALSE;
	}
	
	/**
     * ɾ���ϵ
     * 
     * @access public
	 * @param  string   $column  ΨһPK
	 * @param  int $value  ֵ
     * @return boolean �ɹ����
     */
	public function remove_relationship($column = 'pid', $value)
	{
		$this->db->delete(self::TBL_RELATIONSHIPS, array($column => intval($value))); 
	
		return ($this->db->affected_rows() ==1) ? TRUE : FALSE;
	}
	
	/**
     * ɾ���ϵ
     * 
     * @access public
	 * @param  int   $pid  ����ID
	 * @param  int 	 $mid  meta ID
     * @return boolean �ɹ����
     */
	public function remove_relationship_strict($pid, $mid)
	{
		$this->db->delete(self::TBL_RELATIONSHIPS,
						  array(
						  	'pid'=> intval($pid),
						  	'mid'=> intval($mid)
						 )); 
		
		return ($this->db->affected_rows() ==1) ? TRUE : FALSE;
	}

	/**
    * �޸�����
    * 
    * @access public
	* @param int - $data ������Ϣ
    * @return boolean - success/failure
    */	
	public function update_meta($mid, $data)
	{
		$this->db->where('mid', intval($mid));
		$this->db->update(self::TBL_METAS, $data);
		
		return ($this->db->affected_rows() ==1) ? TRUE : FALSE;
	}

	/**
     * ɾ��һ������
     * 
     * @access public
	 * @param int - $mid ����id
     * @return boolean - success/failure
     */
	public function remove_meta($mid)
	{
		$this->db->delete(self::TBL_METAS, array('mid' => intval($mid))); 
		
		return ($this->db->affected_rows() ==1) ? TRUE : FALSE;
	}
	
}
/* End of file Metas_mdl.php */
/* Location: ./application/models/Metas_mdl.php */