# PHP OOP Analyzer
PHP OOP code analyzer.

<br/>

Usage example (v 0.0.1)
```php
// Files with PHP classes
$files = array("folder/classes.php", "folder/classes2.php");

$analysis = new Analyzer($files);

// Error handling; getClasses() returns a two dimensional array
echo $analysis->error? $analysis->errorMessage : "Successfully analyzed " . count($analysis->getClasses()) . " files.";

// getMEthods() returns a three dimensional array
$files = $analysis->getMethods();

// Loops thru files
foreach($files as $file => $classes)
{
    echo '<p style="border-top: 5px solid #ffffff55; padding-top: 5px;">→ file <strong>' . $file . '</strong></p>';
    echo '<ul>';
    
    // Loops thru classes in the file
    foreach($classes as $class => $methods)
    {
        echo '<li>' . $class . '</li>';
        echo '<ol>';
        
        // Loops thru methods in the class
        foreach($methods as $method)
            echo '<li>' . $method . '</li>';
        echo '</ol>';
    }
    echo '</ul>';
}
```
