<?php
namespace JFileExplorer;
//use Exception;
//use Engine;
//load singleton resource
use JFileExplorer\Engine\Traits\Singleton;
/*
Declare By      : Jafar Abdurrahman Albasyir
Email           : jafarabdurrahmanal-basyir@hotmail.com
Description     : Main class to execute the whole system file
*/
class Exe
{
    //define trait
    //make singleton
    use Singleton;
    //=====>define variable
    private $Configuration      = null;
    //=====>define methode
    /*
    disable free instance
    make this class singleton by access function static Obj
    */
    private function __CONSTRUCT()
    {
        $this->Configuration    = (Object)[
            "Directory"             => (Object)[
                "Root"                  => __DIR__."../../JFileExplorer",
                "Space"                 => "",
            ]
        ];
        //building default configuration
        $this->Initial();
    }



    private function Initial()
    {
        if( !is_dir( $this->Configuration->Directory->Root ) && !file_exists( $this->Configuration->Directory->Root ) )
        {
            mkdir( $this->Configuration->Directory->Root );
        }
    }

    public function Set( $target = null , $value = null )
    {
        //filter param
        if( !is_string( $target ) )
            return false;
        if( !is_string( $value ) )
            return false;
        //bussiness logic
        switch( $target )
        {
            case "ConfRootDir"          : $this->Configuration->Directory->Root = $value; break;
        }
    }
    public function test()
    {
        return "hello";
    }
    private function CheckSession()
    {
        if( $this->Configuration->Directory->Space == "" ) return;
        if( !is_string( $this->Configuration->Directory->Space ) ) throw new Exception\SpaceException("You inserted space not as a string", 1);
        if( $this->Configuration->Directory->Space[0] != "/" && $this->Configuration->Directory->Space[0] != "\\" ) throw new Exception\SpaceException("your string name of space must have symbol '\\' or '/' as postfix on space name exp:'/spaceone' ", 1);

        if( !is_dir( $this->Configuration->Directory->Root.$this->Configuration->Directory->Space ) && !file_exists( $this->Configuration->Directory->Root.$this->Configuration->Directory->Space ) )
        {
            mkdir( $this->Configuration->Directory->Root.$this->Configuration->Directory->Space );
        }
    }
    private function BuildConfigurationAddFile( $config )
    {
        //set default config, to protect from error config
        $configFinal = (object)[
            "path"          => "", //make set in active root and active space
            "source"        => null,
            "name"          => null,
            "overwrite"     => false
        ];

        if( is_array( $config ) )
        {
            $filter = function( &$targetSave , $array , $key )
            {
                if( !array_key_exists( $key , $array ) ) return;
                //if( !is_string( $array[ $key ] ) ) return;
                //if( $array[ $key ] == "" || $array[ $key ] == " " ) return;
                $targetSave = $array[ $key ];
            };
            $filter( $configFinal->path , $config , "path" );
            $filter( $configFinal->source , $config , "source" );
            $filter( $configFinal->name , $config , "name" );
            $filter( $configFinal->overwrite , $config , "overwrite" );
        }
        return $configFinal;
    }
    private function Return( $conf )
    {
        $finalConfig = (object)[
            "bool" => true,
            "message" => (object)[
                "default" => "",
                "throw" => ""
            ],
        ];
        if( is_array( $conf ) )
        {
            if( array_key_exists( "bool" , $conf ) && is_bool( $conf[ "bool" ] ) )                  $finalConfig->bool = $conf[ "bool" ];
            if( array_key_exists( "msgdefault" , $conf ) && is_string( $conf[ "msgdefault" ] ) )    $finalConfig->message->default = $conf[ "msgdefault" ];
            if( array_key_exists( "msgthrow" , $conf ) && is_string( $conf[ "msgthrow" ] ) )        $finalConfig->message->default = $conf[ "msgthrow" ];
        }
        return $finalConfig;
    }
    public function AddFile( $config ) //$path = null , $source , $name = null, $overwrite = false
    {
        //buiding config
        $config = $this->BuildConfigurationAddFile( $config );
        $target_dir = $this->Configuration->Directory->Root.$this->Configuration->Directory->Space.$config->path;
        $File = null;

        try
        {
            $this->CheckSession();
            $File = Engine\Files::Obj()->GetDetailFileUpload( $config->source );
        }
        catch( Exception\SpaceException $err )
        {
            //error on space
            return [];
        }
        catch( Exception\FilesException $err )
        {
            //error on space
           return [];
        }
        //uploading


        try
        {
            //check syncronize beetween type and format
            $tempTypeMain = Engine\MimeType::Obj()->IsContainType( $File->name );
            if( !Engine\MimeType::Obj()->IsMatchingTypeWithFormat( $File->type , $tempTypeMain ) )      return $this->Return( [ "bool" => false , "msgdefault" => "file yang anda masukan diduga script yang membahayakan, tidak diizinkan" ] );

            $targetName = $File->name;
            if( !is_null( $config->name ) )
            {
                $tempType = Engine\MimeType::Obj()->IsContainType( $config->name );
                if( $tempType )
                {
                    if( $tempType != $tempTypeMain )                                                    return $this->Return( [ "bool" => false , "msgdefault" => "untuk saat ini belum mendukung konversi ke format lain" ] );
                    $targetName = $config->name;
                }
                else
                    $targetName = $config->name.$tempTypeMain;
            }
        }
        catch( MimeTypeException $err )
        {

        }
        $tt = $tempTypeMain;
        $target_file = $target_dir . $targetName;
        //set flag as true for default
        $uploadOk = 1;

        try{
            //is image
            if( MimeType::Obj()->IsImageType( $File->type , $tempTypeMain , $File->tmp_name ) ){
                //do something for image type

            }



        }
        catch( MimeTypeException $err)
        {

        }



        // Check if image file is a actual image or fake image
        if(isset($_POST["submit"]))
        {
            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check !== false)
            {
                echo "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
            }
            else
            {
                echo "File is not an image.";
                $uploadOk = 0;
            }
        }
        // Check if file already exists
        if (file_exists($target_file))
        {
            echo "Sorry, file already exists.";
            $uploadOk = 0;
        }
        // Check file size
        if ($_FILES["fileToUpload"]["size"] > 500000)
        {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }
        // Allow certain file formats
        if(
            $imageFileType != "jpg"     &&
            $imageFileType != "png"     &&
            $imageFileType != "jpeg"    &&
            $imageFileType != "gif"
        )
        {
            echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
            $uploadOk = 0;
        }
        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0)
        {
            echo "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
        }
        else
        {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file))
            {
                echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
            }
            else
            {
                echo "Sorry, there was an error uploading your file.";
            }
        }
    }
}
?>
