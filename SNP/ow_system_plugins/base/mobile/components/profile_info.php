<?php

class BASE_MCMP_ProfileInfo extends OW_MobileComponent
{
    /**
     *
     * @var BOL_User
     */
    protected $user;
    protected $previewMode = false;

    public function __construct( BOL_User $user, $previewMode = false )
    {
        parent::__construct();
        
        $this->user = $user;
        $this->previewMode = $previewMode;
    }
    
    public function onBeforeRender() 
    {
        parent::onBeforeRender();
        
        $questionNames = array();
        
        if ( $this->previewMode )
        {
            $questions = BOL_QuestionService::getInstance()->findViewQuestionsForAccountType($this->user->accountType);
            foreach ( $questions as $question )
            {
                if ( $question["name"] == OW::getConfig()->getValue('base', 'display_name_question') )
                {
                    continue;
                }
                
                $questionNames[$question['sectionName']][] = $question["name"];
            }
        }
        
        $questions = BASE_CMP_UserViewWidget::getUserViewQuestions($this->user->id, OW::getUser()->isAdmin(), reset($questionNames));
        
        $this->assign("displaySections", !$this->previewMode);
        $this->assign('questionArray', $questions['questions']);
        $this->assign('questionData', $questions['data'][$this->user->id]);
        $this->assign('questionLabelList', $questions['labels']);
    }
}