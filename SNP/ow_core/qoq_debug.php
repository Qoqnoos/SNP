<?php
/**
 * Developed By Qoqnoos Team.

/**
 * The base class for debugging.
 *
 * @author Alireza Ghadimi <alireza.ghadimi.it@gmail.com>
 * @package ow_core
 * @since 1.0
 */
class OW_QoqDebug
{

	/**
	* Singleton instance.
	*
	* @var OW_QoqDebug
	*/
    private static $classInstance;
	
    
	public function __construct()
    {
        //do nothing.
    }
    
    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return OW_QoqDebug
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    
    
    public function printInfo($var, $echo = true)
    {
        if (!$var)
        {
            $var = 'Empty/False/Null';
        }
        

        if ($echo)
        {
            echo '<pre dir="ltr" style="text-align:left; background:#66ccff; color:#000000; font-family:Tahoma, Verdana, Arial, Helvetica, sans-serif; overflow:auto">'.print_r($var, true).'</pre>';
        }
        else
        {
            return print_r($var, true);
        }
    }
    
    /*
     * When used in some function, prints the call chain that ends to that function.
     */
    public function showCallStack()
    {
    	$callChain = debug_backtrace();
		foreach($callChain as $row)
		{
			$functionChain[] = $row['function'];
			if( isset($row['class']) )
			{
				$classChain[] = $row['class'];
			}
			else
			{
				$classChain[] = ">>Not Set<<";
			}		
		}
		
		$iterator = 1;
		foreach($functionChain as $funcElement)
		{
			echo $iterator . "- " . $funcElement . "<br/>";
			$iterator++;
		}
		
		echo "<hr />";
		
		$iterator = 1;
    	foreach($classChain as $classElement)
		{
			echo $iterator . "- " . $classElement . "<br/>";
			$iterator++;
		}
    }
}