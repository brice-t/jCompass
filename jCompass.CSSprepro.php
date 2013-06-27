<?php
/**
* @package     
* @subpackage  
* @author      Brice Tencé
* @copyright   2012 Brice Tencé
* @link        
* @licence     GNU Lesser General Public Licence see LICENCE file or http://www.gnu.org/licenses/lgpl.html
*/

/**
* plugin for jResponseHTML, which processes Sass files
*/

define( 'COMPILE_TYPE_SASS', 'sass');
define( 'COMPILE_TYPE_SCSS', 'scss');

class jCompassCSSpreproPlugin implements ICSSpreproPlugin {

    private $sassExtensions = array('sass', 'scss');
    private $compassPath = '/usr/bin/compass';
    private $compileType = null;
    private $loadPaths = array();
    private $sassStyle = 'nested'; // nested (default), compact, compressed, or expanded

    public function __construct( CSSpreproHTMLResponsePlugin $CSSpreproInstance ) {

        $gJConfig = jApp::config();

        if( isset($gJConfig->jResponseHtml['CSSprepro_jCompass_extensions']) ) {
            $extString = $gJConfig->jResponseHtml['CSSprepro_jCompass_extensions'];
            $this->sassExtensions = preg_split( '/ *, */', trim($extString) );
        }

        if( isset($gJConfig->jResponseHtml['CSSprepro_jCompass_compasspath']) ) {
            $this->compassPath = $gJConfig->jResponseHtml['CSSprepro_jCompass_compasspath'];
        }

        if( isset($gJConfig->jResponseHtml['CSSprepro_jCompass_style']) ) {
            $this->sassStyle = $gJConfig->jResponseHtml['CSSprepro_jCompass_style'];
        }

        if( isset($gJConfig->jResponseHtml['CSSprepro_jCompass_loadpaths']) ) {
            $this->loadPaths = $gJConfig->jResponseHtml['CSSprepro_jCompass_loadpaths'];
        }
    }


    public function handles( $inputCSSLinkUrl, $CSSLinkParams ) {

        if( in_array( pathinfo($inputCSSLinkUrl, PATHINFO_EXTENSION), $this->sassExtensions ) ||
            (isset($CSSLinkParams['sass']) && $CSSLinkParams['sass']) ) {

                $this->compileType = ( isset($CSSLinkParams['sass']) && $CSSLinkParams['sass'] ?
                    COMPILE_TYPE_SASS :
                    isset($CSSLinkParams['scss']) && $CSSLinkParams['scss'] ?
                    COMPILE_TYPE_SCSS : null );

                if( $this->compileType == null ) {
                    if( 'scss' == pathinfo($inputCSSLinkUrl, PATHINFO_EXTENSION) ) {
                        $this->compileType = COMPILE_TYPE_SCSS;
                    } elseif( 'sass' == pathinfo($inputCSSLinkUrl, PATHINFO_EXTENSION) ) {
                        $this->compileType = COMPILE_TYPE_SASS;
                    } else {
                        trigger_error( 'The Sass type (sass or scss) of ' . $inputCSSLinkUrl . ' could not be determined !', E_USER_WARNING );
                    }
                }

                return true;
            }
    }

    public function compile( $filePath, $outputPath ) {

        $filePathDir = dirname( $filePath );

        $sassProcessArgs = array();

        $sassProcessArgs[] = '-I';
        $sassProcessArgs[] = $filePathDir;

        /*if( $this->compileType == COMPILE_TYPE_SCSS ||
             ($this->compileType === null && 'scss' == pathinfo($filePath, PATHINFO_EXTENSION)) ) {
            $sassProcessArgs[] = '--scss';
          }*/

        $sassProcessArgs[] = '--output-style';
        $sassProcessArgs[] = $this->sassStyle;

        foreach ($this->loadPaths as $loadPath) {
            $sassProcessArgs[] = '-I';
            $sassProcessArgs[] = $loadPath;
        }

        //building compass config
        $configTemp = tempnam( sys_get_temp_dir(), 'jCompassConfig' );
        $configTempHandle = fopen( $configTemp, 'w' );
        fwrite( $configTempHandle, 'http_path = "' . jApp::config()->urlengine['basePath'] . '"' . "\n" );
        fwrite( $configTempHandle, 'css_dir = ""' . "\n" );
        fwrite( $configTempHandle, 'sass_dir = ""' . "\n" );
//images_dir = "../Images"
        fclose( $configTempHandle );

        $sassProcessArgs[] = '--config';
        $sassProcessArgs[] = $configTemp;

        // input
        $tempString = uniqid('jCompass', true);
        $inputTempPath = $filePathDir . DIRECTORY_SEPARATOR . $tempString . ( $this->compileType == COMPILE_TYPE_SASS ? '.sass' : '.scss' );
        $outputTempPath = $filePathDir . DIRECTORY_SEPARATOR . $tempString . '.css';
        file_put_contents( $inputTempPath, file_get_contents($filePath) );
        $sassProcessArgs[] = dirname($inputTempPath);
        $sassProcessArgs[] = $inputTempPath;


        $sassProcessArgs = array_map( 'escapeshellarg', $sassProcessArgs );
        $sassProcessCmd = $this->compassPath . ' compile ' . implode(' ', $sassProcessArgs) . ' 2>&1';

        exec( $sassProcessCmd, $sassProcessOutput, $code );

        unlink( $inputTempPath );
        unlink( $configTemp );

        if( $code !== 0 ) {
            if( file_exists( $outputTempPath ) ) {
                unlink( $outputTempPath );
            }
            trigger_error( "Compass error (returned $code) for '$filePath' : " . implode("\n", $sassProcessOutput), E_USER_ERROR );
        }

        file_put_contents( $outputPath, file_get_contents( $outputTempPath ) );
        unlink( $outputTempPath );
    }


    public function cleanCSSLinkParams( & $CSSLinkParams ) {
        unset($CSSLinkParams['sass']);
    }

}


