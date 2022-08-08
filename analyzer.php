<?php

/*
 * PHP OOP Analyzer
 * 
 * Copyright (c) 2022 php-oop-analyzer (https://github.com/Foortec/php-oop-analyzer)
 * MIT License
 */

namespace foortec\analyzer;
use ReflectionClass, ReflectionMethod;

class Analyzer
{
    private array|string $filepath;

    private array $classes;
    private array $methods;

    public bool $error = false;
    public string $errorMessage = "";
    const INVALID_PATH = "Invalid path.";
    const FILE_OPEN_ERROR = "File missing or permission denied.";

    public function __construct(array|string $filepath = ".")
    {
        if(is_array($filepath))
        {
            for($i=0; $i<count($filepath); $i++)
                $this->filepath[$i] = htmlentities($filepath[$i]);
            $this->checkInput(true);
            if(!$this->error)
                $this->analyze();
            return;
        }
        
        $this->filepath = htmlentities($filepath);
        $this->checkInput();
        if(!$this->error)
            $this->analyze();
    }

    private function error(string $errorMessage = "No message.") : void
    {
        $this->error = true;
        $this->errorMessage = $errorMessage;
    }

    private function checkInput(bool $array = false) : void
    {
        if(!$array && !file_exists($this->filepath))
        {
            $this->error(self::INVALID_PATH);
            return;
        }

        if(!is_array($this->filepath))
        {
            if(!file_exists($this->filepath))
                $this->error(self::INVALID_PATH);
            return;
        }

        for($i=0; $i<count($this->filepath); $i++)
        {    
            if(!file_exists($this->filepath[$i]))
            {
                $this->error(self::INVALID_PATH);
                break;
            }
        }
    }

    private function getFileLines(string $filepath) : array
    {
        $file = fopen($filepath, "r") or $this->error(self::FILE_OPEN_ERROR);
        if($this->error)
            return array();
        $fileContent = fread($file, filesize($filepath));
        fclose($file);
        $fileContent = str_replace("\n\r", "\n", $fileContent);
        return explode("\n", $fileContent);
    }

    private function getClassNamesFromLines(array $lines) : array
    {
        $classNamesIter = 0;

        for($i=0; $i<count($lines); $i++)
        {
            if(preg_match(";^\s*class|\s*final class|\s*abstract class;", $lines[$i]) === 1)
            {
                $lines[$i] = preg_replace(";^\s*class\s*|\s*final class\s*|\s*abstract class\s*|\s*extends\s*\w*|\s*implements\s*\w*\s*;", "", $lines[$i]);
                $classNames[$classNamesIter] = preg_replace(";\s*{\s*$;", "", $lines[$i]);
                $classNamesIter++;
            }
        }
        return $classNames;
    }

    private function getClassLines() : array
    {
        if(is_array($this->filepath))
        {
            for($i=0; $i<count($this->filepath); $i++)
            {
                $fileLines = $this->getFileLines($this->filepath[$i]);
                if($this->error)
                    continue;

                $classNames[$this->filepath[$i]] = $this->getClassNamesFromLines($fileLines);
            }
            return $classNames;
        }

        $fileLines = $this->getFileLines($this->filepath);
        if($this->error)
            return array();

        $classNames[$this->filepath] = $this->getClassNamesFromLines($fileLines);
        return $classNames;
    }

    private function getMethodNames() : array
    {
        if(!isset($this->classes))
            $this->classes = $this->getClassLines;
        
        $methodNames = array();
        if(is_array($this->filepath))
        {
            foreach($this->filepath as $file)
                require($file);

            for($i=0; $i<count($this->filepath); $i++)
            {
                for($j=0; $j<count($this->classes[$this->filepath[$i]]); $j++)
                {
                    $reflector = new ReflectionClass($this->classes[$this->filepath[$i]][$j]);
                    $methods = $reflector->getMethods();
                    for($k=0; $k<count($methods); $k++)
                        $methodNames[$this->filepath[$i]][$methods[$k]->class][$k] = $methods[$k]->name;
                }
            }
            return $methodNames;
        }

        require($this->filepath);

        for($j=0; $j<count($this->classes[$this->filepath]); $j++)
        {
            $reflector = new ReflectionClass($this->classes[$this->filepath][$j]);
            $methods = $reflector->getMethods();
            for($k=0; $k<count($methods); $k++)
                $methodNames[$this->filepath][$methods[$k]->class][$k] = $methods[$k]->name;
        }
        return $methodNames;
    }

    private function analyze() : void
    {
        $this->classes = $this->getClassLines();
        $this->methods = $this->getMethodNames();
    }

    public function getClasses() : array
    {
        return $this->classes;
    }

    public function getReflectionClasses() : array
    {
        $i=0;
        foreach($this->classes as $classes)
        {
            foreach($classes as $class)
            {
                $return[$i] = new ReflectionClass($class);
                $i++;
            }
        }
        return $return;
    }

    public function getMethods() : array
    {
        return $this->methods;
    }

    public function getReflectionMethods() : array
    {
        $i=0;
        foreach($this->methods as $file => $classes)
        {
            foreach($classes as $class => $methods)
            {
                foreach($methods as $method)
                {
                    $return[$i] = new ReflectionMethod($class, $method);
                    $i++;
                }
            }
        }
        return $return;
    }
}