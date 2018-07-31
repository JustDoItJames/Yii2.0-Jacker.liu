<?php
namespace app\models\thrift;
use app\components\utils\Code;
use app\components\utils\Formator;
use yii;
use yii\log\Logger;

class UserCenterThriftService extends ThriftService
{
	public $service = "ThriftUserInfoService";
	
	/**
	 * @param string userId
	 * @return array list of \shumitradeinfo\TPageUserCombineFundShares. 
	 * An integer error code will be returned if an error occurred.
	 */
	public function getUserBasicInfo($userId)
	{
		if (!preg_match('/^[1-9]\d*$/', $userId)) {
			$this->errCode = Code::PARAM_ERR;
			$this->errMsg = '用户id格式错误!';
			return $this->getErrCode();
		}
		
		if (!$this->invoke("getBasicUserInfo", (float) $userId)) {
			return $this->getErrCode();
		}
		return $this->data();
	}
	
	/**
	 * 添加用户身份证信息
	 * @param string $userId
	 * @param string $cardName
	 * @param string $cardNubmer
	 * @return boolean
	 */
	public function addIdentityCardInfo($userId, $cardName, $cardNubmer)
	{
		if (!preg_match('/^[1-9]\d*$/', $userId)) {
			$this->errCode = Code::PARAM_ERR;
			$this->errMsg = '用户id格式错误!';
			return false;
		}
		
		if (!$this->invoke("addIdentityCardInfo", (float) $userId, 
				new \userinfocenter\IdentityCardInfo(array(
						"identityCardName"=>$cardName, "identityCardNumber"=>$cardNubmer)))) {
			return false;
		}
		return (bool) $this->data();
	}


    /**
     * 检查身份证是否存在
     */
    public function isIdentityCardNumberExist($idCard)
    {
        if(!Formator::isIdCard($idCard)) {
            $this->errCode = Code::USER_IDCARD_EXIST;
            $this->errMsg = 'bad id card format!';
            return false;
        }

        if(!$this->invoke('isIdentityCardNumberExist',$idCard)) {
            return false;
        }

        return (bool)$this->data();
    }

    /**
     * 新增用户私募风险评测
     */
    public function addUserPFRiskEvaluationInfo($params){

        $info = new \userinfocenter\UserPFRiskEvaluationInfo();
        $info->answer = $params['data'];
        $info->point = $params['total'];
        $info->risk_type = $params['type'];
        $info->platform_id = 1;
        $info->topic_id = 2;
        $info->uid = (float)$params['uid'];
        $info->phone = $params['phone'];
        Yii::getLogger()->log('params:' . json_encode($params), Logger::LEVEL_TRACE);

        if (!$this->invoke("addUserPFRiskEvaluationInfo", $info)) {

            return false;
        }

        return (bool)$this->data();
    }

    /**
     * 更新用户私募风险评测
     */
    public function updateUserPFRiskEvaluationInfo(\userinfocenter\UserPFRiskEvaluationInfo $info){

        if (!$this->invoke("updateUserPFRiskEvaluationInfo", $info)) {
            return false;
        }
        return (bool)$this->data();
    }

    /**
     * 获取用户私募风险评测
     */
    public function getUserPFRiskEvaluationInfo($uid){

        if (!$this->invoke("getUserPFRiskEvaluationInfo", (float) $uid)) {
            return $this->getErrCode();
        }
        return $this->data();
    }

    /**
     * 根据手机号获取用户私募风险评测
     */
    public function getUserPFRiskEvaluationInfoByPhone($phone){

        if (!$this->invoke("getUserPFRiskEvaluationInfoByPhone", $phone)) {
            return $this->getErrCode();
        }
        return $this->data();
    }


}