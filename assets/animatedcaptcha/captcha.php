<?
if(!isset($_SESSION))
	session_start();
/*
This Animated Gif Captcha system is brought to you courtesy of ...
josh@betteradv.com                                    ==> Josh Storz
http://www.querythe.net/Animated-Gif-Captcha/         ==> Download Current Version

OOP (PHP 4 & 5) Interface by ...
krakjoe@krakjoe.info                                  ==> J Watkins

The GIFEncoder class was written by ...
http://gifs.hu                                        ==>  László Zsidi
http://www.phpclasses.org/browse/package/3163.html    ==>  Download Current Version 

This file is part of QueryThe.Net's AnimatedCaptcha Package.

QueryThe.Net's AnimatedCaptcha is free software; you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation; either version 2.1 of the License, or
(at your option) any later version.

QueryThe.Net's AnimatedCaptcha is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with QueryThe.Net's AnimatedCaptcha; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA 
*/

class AnimCaptcha
{
	static $frames;
	static $time;
	static $num;
	static $pause;
	static $ops;
	static $gifs;
	static $rand;
	static $math;
	
	function AnimCaptcha( $gifs, $pause )
	{
		if( !class_exists( 'GIFEncoder' ) and !include('GIFEncoder.class.php') )
			die( 'I require GIFEncoder to be loaded before operation' );	

		self::$pause = (int)$pause;
		self::$gifs = $gifs;
		self::$ops = array
		(
			'minus',
			'plus',
			'times'
		);
		self::$math = array
		(
			'-',
			'+',
			'*'
		);
		
		self::$num['rand1'] = rand( 1, 9 );
		self::$num['rand2'] = rand( 1, 9 );
		self::$num['op'] = rand( 0, count( self::$math ) - 1 );
		
		self::BuildImage( );
	}
	function BuildImage( )
	{
		self::$frames[ ] = sprintf( '%s/solve.gif', self::$gifs );
    	self::$time[ ]	 = 260;  
    	self::$frames[ ] = sprintf( '%s/%d.gif', self::$gifs, self::$num['rand1'] );
    	self::$time[ ]	 = self::$pause;
		self::$frames[ ] = sprintf( '%s/%s.gif', self::$gifs, self::$ops[ self::$num['op'] ] );
    	self::$time[ ]	 = self::$pause;
    	self::$frames[ ] = sprintf( '%s/%d.gif', self::$gifs, self::$num['rand2'] );
    	self::$time[ ]	 = self::$pause;
    	self::$frames[ ] = "frames/equals.gif";
    	self::$time [ ]  = 280;
	}
	function GetImage( )
	{	
		eval( sprintf( '$_SESSION["captcha"][$_GET["fid"]][$_GET["eid"]] = (%d %s %d);', 
										self::$num['rand1'], 
										self::$math[ self::$num['op'] ], 
										self::$num['rand2']
		) );
		
		if( $_SESSION['answer'] < 0 ) 
			self::AnimCaptcha( self::$gifs, self::$pause );
		
		$gif = new GIFEncoder( self::$frames, self::$time, 0, 2, 0, 0, 0, "url" );
		
		if( !headers_sent( ) )
		{
			header ( 'Content-type:image/gif' );
			echo $gif->GetAnimation ( );
		}
	}
}
new AnimCaptcha( 'frames', 140 );
AnimCaptcha::GetImage( );
?>
