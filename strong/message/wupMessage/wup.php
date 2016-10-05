<?php

require_once('jce.php');
require_once('RequestF_wup.php');

class UniPacket
{
	protected $_iVer;
	protected $_data;
	protected $_new_data;
	
	public function __construct()
	{
		$this->_iVer = new c_short;
		
		$this->_iVer->val = 2;
		//v2
		$this->_data = new c_map(new c_string,new c_map(new c_string,new c_vector(new c_char)));
		//v3
		$this->_new_data = new c_map(new c_string,new c_vector(new c_char));
	}
	
	public function setVersion($version)
	{
		$this->_iVer->val = $version;
	}
	
    public function put($name,$mystruct)
    {
        //��structд��$struct_data��ȥ
		if(method_exists($mystruct,'writeTo'))
		{
			$mystruct->writeTo($struct_data,0);
        }else
		{
			$mystruct->write($struct_data,0);
		}
		
		if($this->_iVer->val == 3)
		{
			$vector = new c_vector(new c_char);
			$vector->push_back($struct_data);
			$this->_new_data->push_back($name,$vector);
		}
		else
		{
			//������map�� data��$vector���������������value�ĵط� $struct�������������Ϊkey
			$vector = new c_vector(new c_char);        	
			$struct = new c_map(new c_string,$vector);	
			//vector��������
			$vector->push_back($struct_data);	
			
			//stucte��map����������Ϊkey����Ӧ�ö���ĵ�vector
			$struct->push_back($mystruct->get_class_name(),$vector);
			
			//������һ�����map data��name��Ϊkey��value�������map
			$this->_data->push_back($name,$struct);
		}		
    }
	
	// ����name��Ϊkey��ȡ�ûذ�struct��Ϣ
    public function get($name,&$mystruct)
    {   
		if($this->_iVer->val == 3)
		{
			$struct_vec = $this->_new_data->get_val($name);
			//����vector��õ�
			if(method_exists($mystruct,'readFrom'))
			{
				$mystruct->readFrom($struct_vec->get_val(),0); 
			}
			else
			{
				$mystruct->read($struct_vec->get_val(),0); 
			}
		}
		else
		{
			//��һ����nameΪkey
			$struct_map = $this->_data->get_val($name);
            //�ڶ�����������Ϊkey
			$struct_vec = $struct_map->get_val($mystruct->get_class_name());
			//����vector��õ�

            if(method_exists($mystruct,'readFrom'))
			{
				$mystruct->readFrom($struct_vec->get_val(),0); 
			}
			else
			{
                $v = $struct_vec->get_val();
				$mystruct->read($v,0);
			}
		}     
    }
	public function _encode(&$stream)
	{
		if($this->_iVer->val == 3)
		{
			$this->_new_data->write($stream,0);			
		}
		else
		{
			$this->_data->write($stream,0);
		}
	}
	public function _decode(&$stream)
	{
		//�ȼ����stream�Ƿ�Ϊ�գ��������������Ϸ���ʱ��server���ذ�û�����body���ݵ�ʱ��ᷢ��
    	if(strlen($stream) == 0 || is_null($stream))
    	{
    		throw new JCEException(__LINE__,STREAM_LEN_ERROR);	
    		return false;
    	}
    	
		if($this->_iVer->val == 3)
		{
			$this->_new_data->clear();
			$this->_new_data->read($stream,0);
		}
		else
		{
			$this->_data->clear();
			$this->_data->read($stream,0);
		}
	}
}

class wup_unipacket extends UniPacket
{
	protected $requestPacket;
	
    public function __construct()
    {
		parent::__construct();
		$this->requestPacket = new RequestPacket;	
		$this->setVersion(2);
    }
	
    public function setVersion($version)
    {
		parent::setVersion($version);
		$this->requestPacket->iVersion->val = $version;
    }
	public function getVersion() {return $this->requestPacket->iVersion;}
    //���������ID
    public function setRequestId($id)
    {	
		$this->requestPacket->iRequestId->val = $id;
    }
	
	public function getRequestId() {return $this->requestPacket->iRequestId;}
	
    //���÷�������
    public function setServantName($name)
    {
        $this->requestPacket->sServantName->val = $name;
    } 
	public function getServantName() {return $this->requestPacket->sServantName;}
	
    //���ú�������
    public function setFuncName($name)
    {
        $this->requestPacket->sFuncName->val = $name;
    }
	public function getFuncName() {return $this->requestPacket->sFuncName;}
    //������� ����stream
    public function _encode(&$stream)
    {             
		if($this->_iVer->val == 3)
		{ 
			$this->_new_data->write($data_stream,0); 
		}
		else
		{
			$this->_data->write($data_stream,0);
		} 
		
		//��д��UniPacket����
		$this->requestPacket->sBuffer->push_back($data_stream);
		//��д��requestPacket
		$this->requestPacket->writeTo($stream,0);
			 
        //��stream����ǰ�棬����stream�ĳ��ȣ�int����
        $len = strlen($stream)+4;  
        $stream = pack('N',$len).$stream;
    }
    
    public function _decode(&$stream)
    {        
    	//�ȼ����stream�Ƿ�Ϊ�գ��������������Ϸ���ʱ��server���ذ�û�����body���ݵ�ʱ��ᷢ��
    	if(strlen($stream) == 0 || is_null($stream))
    	{
    		throw new JCEException(__LINE__,STREAM_LEN_ERROR);	
    		return false;
    	}
    	
    	//WUP�İ���ͷǰ����wup���ĳ���
        $len = @unpack('N',$stream);
        $len = $len[1];
		
        if(intval($len) <= 0 )
        {	
        	throw new JCEException(__LINE__,STREAM_LEN_ERROR);
        	return false;
        }
		
		//Э���ͷ��ȡ��ȥ
        $stream = substr($stream,4);

		//�Ƚ���requestPacket
		$this->requestPacket->readFrom($stream,0);

		$this->_iVer = $this->requestPacket->iVersion;

		if($this->_iVer->val == 3)
		{ 
			$this->_new_data->read($this->requestPacket->sBuffer->get_val(),0); 
		}
		else
		{
			$v = $this->requestPacket->sBuffer->get_val();
			$this->_data->read($v,0);
		}
    }  
	public function getResultCode()
	{
		$ret = $this->requestPacket->status->get_val('STATUS_RESULT_CODE');
		return intval($ret->val);
	}
	
	public function getResultDesc()
	{
		$ret = $this->requestPacket->status->get_val('STATUS_RESULT_DESC');
		return $ret->val;
	}
}

class wup_ResponsePacket
{
    public $iVersion;//Э��汾��
    public $cPacketType;//��������
    public $iRequestId;//��������ID��
    public $iMessageType;//��Ϣ����
    public $iRet;
    public $sBuffer;//���ݻ��棬����Ҫ���ͻ��߽��ܵ����ݣ��������߽���ǰ������
    public $status;//������Ϣ��״ֵ̬
    public $sResultDesc;//�����ʱ�����ڰ�buffer��ԭΪ�ɱ�ʶ�Ķ���
    public function __construct()
    {
        $this->iVersion     = new c_short;
        $this->cPacketType  = new c_char;
        $this->iMessageType = new c_int;
        $this->iRequestId   = new c_int;
        $this->iRet           = new c_int;
        $this->sBuffer      = new c_vector(new c_char);
        $this->status       = new c_map(new c_string,new c_string);  
        $this->sResultDesc= new c_string;
        $this->iVersion->val    = 1;
        $this->cPacketType->val = 0;
        $this->iMessageType->val = 0;
        $this->iRequestId->val   = 0;
    }
    public function get_version($version)
    {
           return $this->iVersion;
    }
    //���������ID
    public function get_request_id($id)
    {
        return $this->iRequestId;
    }
    //������� ����stream
    public function _encode(&$stream)
    {
    	//��Э�����ر���д��stream
        $this->iVersion->write($stream,1);
        $this->cPacketType->write($stream,2);        
        $this->iMessageType->write($stream,3);        
        $this->iRequestId->write($stream,4);
        $this->iRet->write($stream,5);
        $this->sBuffer->write($stream,6); 
        $this->status->write($stream,7);
        $this->sResultDesc->write($stream,8);
        //��stream����ǰ�棬����stream�ĳ��ȣ�int����
        $len = strlen($stream)+4;  
        $stream = pack('N',$len).$stream;
    }
    public function _decode(&$stream)
    {        
    	//�ȼ����stream�Ƿ�Ϊ�գ��������������Ϸ���ʱ��server���ذ�û�����body���ݵ�ʱ��ᷢ��
    	if(strlen($stream) == 0 || is_null($stream))
    	{
    			
    		return false;
    	}
    	//WUP�İ���ͷǰ����wup���ĳ���
        $len = @unpack('N',$stream);
        $len = $len[1];
        if(intval($len) <= 0 )
        {	
        	
        	return false;
        }
        $stream = substr($stream,4);
        $this->iVersion->read($stream,1);
        $this->cPacketType->read($stream,2);        
        $this->iMessageType->read($stream,3);        
        $this->iRequestId->read($stream,4);        
        $this->iRet->read($stream,5);        
        $this->sBuffer->read($stream,6);
        $this->status->read($stream,7);
        $this->sResultDesc->read($stream,8);
    }          
}
?>
