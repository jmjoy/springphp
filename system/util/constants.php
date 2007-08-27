<?php
/*
 * Copyright 2007 the original author or authors.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

define('PATH_WITHIN_HANDLER_MAPPING_ATTRIBUTE', 'pathWithinHandlerMapping');

define('REDIRECT_URL_PREFIX', 'redirect:');

define('DEFAULT_CONTENT_TYPE', "text/html; charset=UTF-8");

define('ORIGINAL_DEFAULT_THEME_NAME', "theme");

define('DEFAULT_PATH_SEPARATOR', '/');

define('EXTENSION_SEPARATOR', '.');

// validation / binding
define('DEFAULT_OBJECT_NAME', 'target');
define('NESTED_PATH_SEPARATOR', "_");
define('CODE_SEPARATOR', '.');
define('BINDING_RESULT_MODEL_KEY_PREFIX', 'bindingResult_');
define('MISSING_FIELD_ERROR_CODE', 'required');
define('TYPE_MISMATCH_ERROR_CODE', 'typeMismatch');

define('NESTED_PROPERTY_SEPARATOR',"_");
define('PROPERTY_KEY_PREFIX', "[");
define('PROPERTY_KEY_SUFFIX',"]");

define('DEFAULT_FIELD_MARKER_PREFIX', '_');

// command controllers
define('DEFAULT_COMMAND_NAME', 'command');
define('COMMAND_NAME_REQUEST_ATTRIBUTE', 'commandName');

// tags
define('NESTED_PATH_VARIABLE_NAME', 'nestedPath');
define('COMMAND_NAME_VARIABLE_NAME', 'commandName');


define('LOCALE_RESOLVER_SERVICE', "localeResolver");
define('THEME_RESOLVER_SERVICE', "themeResolver");
define('HANDLER_MAPPING_SERVICE', "handlerMapping");
define('HANDLER_ADAPTER_SERVICE', "handlerAdapter");
define('REQUEST_TO_VIEW_NAME_TRANSLATOR_SERVICE', "viewNameTranslator");
define('VIEW_RESOLVER_SERVICE', "viewResolver");

//pagination
define('SORTBY_NVP', 'sortBy');
define('PAGINATION_NVP', 'page');



define('SC_CONTINUE', '100 Continue');
define('SC_SWITCHING_PROTOCOLS', '101 Switching Protocols');
define('SC_OK', '200 OK');
define('SC_CREATED', '201 Created');
define('SC_ACCEPTED', '202 Accepted');
define('SC_NON_AUTHORITATIVE_INFORMATION', '203 Non Authoritative Information');
define('SC_NO_CONTENT', '204 No Content');
define('SC_RESET_CONTENT', '205 Reset Content');
define('SC_PARTIAL_CONTENT', '206 Partial Content');
define('SC_MULTIPLE_CHOICES', '300 Multiple Choices');
define('SC_MOVED_PERMANENTLY', '301 Moved Permanently');
define('SC_MOVED_TEMPORARILY', '302 Found');
define('SC_SEE_OTHER', '303 See Other');
define('SC_NOT_MODIFIED', '304 Not Modified');
define('SC_USE_PROXY', '305 Use Proxy');
define('SC_TEMPORARY_REDIRECT', '307 Temporary Redirect');
define('SC_BAD_REQUEST', '400 Bad Request');
define('SC_UNAUTHORIZED', '401 Unauthorized');
define('SC_PAYMENT_REQUIRED', '402 Payment Required');
define('SC_FORBIDDEN', '403 Forbidden');
define('SC_NOT_FOUND', '404 Not Found');
define('SC_METHOD_NOT_ALLOWED', '405 Method Not Allowed');
define('SC_NOT_ACCEPTABLE', '406 Not Acceptable');
define('SC_PROXY_AUTHENTICATION_REQUIRED', '407 Proxy Authentication Required');
define('SC_REQUEST_TIMEOUT', '408 Request Timeout');
define('SC_CONFLICT', '409 Conflict');
define('SC_GONE', '410 Gone');
define('SC_LENGTH_REQUIRED', '411 Length Required');
define('SC_PRECONDITION_FAILED', '412 Precondition Failed');
define('SC_REQUEST_ENTITY_TOO_LARGE', '413 Request Entity Too Large');
define('SC_REQUEST_URI_TOO_LONG', '414 Request-URI Too Long');
define('SC_UNSUPPORTED_MEDIA_TYPE', '415 Unsupported Media Type');
define('SC_REQUESTED_RANGE_NOT_SATISFIABLE', '416 Requested Range Not Satisfiable');
define('SC_EXPECTATION_FAILED', '417 Expectation Failed');
define('SC_INTERNAL_SERVER_ERROR', '500 Internal Server Error');
define('SC_NOT_IMPLEMENTED', '501 Not Implemented');
define('SC_BAD_GATEWAY', '502 Bad Gateway');
define('SC_SERVICE_UNAVAILABLE', '503 Service Unavailable');
define('SC_GATEWAY_TIMEOUT', '504 Gateway Timeout');
define('SC_HTTP_VERSION_NOT_SUPPORTED', '505 HTTP Version Not Supported');

define('HEADER_PRAGMA', 'Pragma');
define('HEADER_EXPIRES', 'Expires');
define('HEADER_CACHE_CONTROL', 'Cache-Control');

?>
