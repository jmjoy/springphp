# History of Spring PHP #

Spring PHP emerged from my need for a true Web MVC framework that was deployable on a shared LAMP hosting environment.  After looking at many PHP frameworks, none seemed to fit the requirements:

  * PHP4 & PHP5
  * Object Oriented - work with objects rather than global arrays and functions
  * MVC2 design model - controllers push the model to views
  * IoC Container - to eliminate implementation-specific dependencies between objects
  * Light Weight
  * Simple Installation - no complex deployment, no command line, no compilation
  * No use of large-scale libraries like PEAR
  * No templating language to learn - uses native PHP for views
  * Completely flexible
  * Easily portable to Java (Spring Framework)
  * Broad compatibility - able to be deployed across many environments
  * Performance - but not at the cost of flexibility
  * Self-contained and configurable libraries for specific features
  * No "spaghetti code" processing flow to understand
  * Fosters, if not demands, clean, maintainable, and TESTABLE code
  * Integrated logging for easier debugging
  * Able to quickly integrate external libraries


The goals of the Spring Framework for Java hold true for Spring PHP:

  * Make developing a complete webapp in PHP easier and LESS COMPLEX
  * Promote good programming using best practices & proven design patterns
  * Reduce the need for "reinventing the wheel"
  * Make existing technologies easier to use
  * Provide one consistent method for configuring the application
  * Whenever possible, your application code should NOT depend upon SpringPHP APIs


CodeIgniter libraries....

Akelos' RoR inspiration tags...


# The IoC Container #

The IoC container, central to the framework, provides a consistent means of configuring and managing PHP objects.  It is represented in SpringPHP by the AppContext object.

The container is responsible for creating objects, configuring objects, calling initialization methods, reading property files, assigning property values to objects, and passing objects to registered callback objects.  Objects created by the container are referred to as "Managed Objects", often called "Beans" in the Java realm, but also referred to as "Service Points" in the SpringPHP framework.  The container is configured using a YAML file that contains Service Point definitions.  These provide all the information needed to create objects.  Once objects are created and configured without error, they become available for usage.

Dependency Injection is a design pattern that describes when a fully configured object (perhaps a database connection) is passed into a second object to provide a particular needed capacity.  This prevents the need for the second object to create and configure the object internally.  In SpringPHP, any object (library, DAO, etc) or property (configuration parameter) that your object depends upon is handed to you, fully configured or assigned value and ready to be used, by the IoC container.  No need for factory lookups, class loading, making database connections manually, reading configuration files, etc.

It's recommended that all objects managed by the container are assigned to one another using Dependency Injection.  Although it is quite possible to explicitly include, create, and configure an instance of an object within your application code, it does not follow the best practice model of "Dependency Injection" that the IoC container provides for you.  It's also possible to manually lookup the object by name using the AppContext object, but this is also not recommended.

The benefit of this is simple.  Your objects have no dependencies on a specific implementation of a class.  Any layer within the application can change implementation without the need to rewrite any other layer.  For example, by abstracting the data layer with "Data Access Objects" and by using objects (instead of result sets) within your application, you could not only switch database engines (from MySQL to PostgreSQL, for example) but completely change database table structures without the need to rewrite any business logic or view code.

## IoC Container Configuration ##

The entire IoC container is driven by YAML configuration files.  It is the configuration metadata that informs the SpringPHP container how to "instantiate, configure, and assemble the objects within your application".

It manages the 2 major features of the application:  Property Points (simple property files), and Service Points (managed objects that could include controllers, strategy implementations, libraries, data access objects, etc).

### Property Points ###

Property Points represent property files that contain important configuration information, such as database connection values.  This provides the means for separating configuration parameters from application code.

A property file is simply a PHP file containing an array named $properties.  Any array key you define becomes an available "property" able to be assigned within the application context.  Properties are specified using the syntax "!!property arrayKeyName".

NOTE: the idea here is to prevent certain configuration parameters (db connection strings) from being served as plain text as the YAML configuration file or a plain text property file.

(insert example of a property file, how to include it within the context, and how to assign them to object properties)

Property Point definition:

```
property-points:
	- (file location)
```

### Service Points ###

Service Points represent the managed and configurable objects within your application.  Service Points follow two models: Prototype and Singleton.  ...

Service Point definition:

```
service-points:

	name:
		description: (description of the service point)
		base: (base directory - [!!constant (name)] provides ability to use a CONSTANT as a root path - otherwise uses config file's directory as root*)
		extends: (abstract-service-point name)
		model: (singleton* | prototype)
		aliases:
			- (alias)
			- (alias)
		implementor:
			class: (file location)::(class name)
			autowire: (true | false*)
			parameters:   #constructor arguments
				(name): (value)
				(name): (value)
			properties:   #object properties
				(name): (value [!!property (name) | !!service (name) | !!constant (name) | !!autowire (file)::(class)])
				(name): (value)
			invoke:       #post-constructor methods to invoke
				(method name): 
					- (parameter1)
					- (parameter2)
				(method name): (parameter1)
			initialize-method: (method name)  #no-arg initialization method
```


## Autowiring ##

"Autowiring" of objects, defined by "autowire: true" by the service-point definition, reduces the need for complex Service Point configurations.  By specifying a Service Point to be autowired, the AppContext will automatically assign the configured object to any properties of the original object that match the same name as a Service Point.  For example, if you had a DAO that needed access to the Datasource object, instead of explicitly defining the property as:

```
	properties:
		datasource: !!service datasource
```

You could add a property, or object member, to your DAO object called "$datasource", and immediately access the Datasource service - the AppContext takes care of the assignment.

A property on an object can also include and autowire a class with a zero-arg constructor.  The application context will autowire the class with any properties it might define.  For instance, if your controller needed to use a validator that didn't require a specific context definition, you could assign it to the validators property like so:

```
	properties:
		validators:
			- !!autowire logic/validators/UserValidator.php::UserValidator
```


## Interfaces - Non-Specific Implementations ##

Although PHP4 lacks explicit "interfaces", by using the IoC container, the idea is still there.  A developer is coding to a non-specific implementation that has been handed to him by the container, rather than explicitly accessing a particular class.


# Java "Container" vs. PHP Procedural Code #

All requests to Spring PHP are routed through a single point of entry: index.php.  Upon inspecting index.php, you'll find a very simple procedure:

  1. Load the system
  1. Instantiate the 3 globals: Request, Response, and AppContext
  1. Start the Response's output buffering
  1. Load the application
  1. Load the IoC container / application context - (defined in APPPATH./config/app-context.yml)
  1. Auto-discover and load any plugins
  1. Create the Dispatcher service
  1. Call the Dispatcher's process method, passing the request & response
  1. Flush the Response's output buffer


Java Servlet architecture mimic...


# Application Objects #

```

Controllers
Models w/ ClassInfo
Views

Interceptors
Validators
Forms
DAOs

--Optional--

Themes
Plugins

```

# System Objects #

```
--System Layer--

	IoC Container - AppContext

	Exceptions

	HTTP
		Request
		Response
		Cookie
		Session
		UploadedFile
		UserAgent
		Locale
		RequestUtils

	Dispatcher
		LocaleResolver
		ThemeResolver
		HandlerMapping
		RequestToViewNameTranslator
		ViewResolver

	Libraries
		Log
		Benchmark
		ImageManipulation
		Email
		I18n
		Encryption

	Functions & Classes - util
		Date
		Time
		Timestamp
		Pagination
		Sort

	tags
		form tags
		asset tags
		javascript tags
		pagination tags


--Controller Layer--

	Command Controllers
		PropertyEditor
		PropertyEditorRegistrar
		ValidationUtils
		SimpleFormController
		bind tags
	
	View Controllers
		ParameterizableViewController
		UrlFilenameViewController


--View Layer--

	ModelAndView

	Views
		PHPView
		RedirectView
		DocumentView?

--Model / Data Layer--

	Datasource
	SQLMapper
	DB/ActiveRecord library

```