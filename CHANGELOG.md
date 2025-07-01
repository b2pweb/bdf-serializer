v1.3.0
------

* Add support for `readonly` properties and classes (#FRAM-168) (#13)

v1.1.4
------

* Fixed the null value should be removed only if set as default on typed properties (#FRAM-72) (#11)


v1.1.3
------

* Fixed the serialization of null value for typed properties. The option `null` is not considered for typed properties (#FRAM-72) (#10).


v1.1.2
------

* Ignore array or object structure notation on `@var` annotation
* Add checkstyle


v1.1.0
------
* Adding support of virtual property
* Adding support of annotations from JMS serializer
* Adding support of php 7.4 typed properties (#4)
* Fixed usage of builtin type int and bool


v1.0.2
------
* bug #3 Disable inline property when the meta type option is provided


v1.0.1
------
* feature #2 Add the inline option in the metadata builder
* bug #1 Fixes parameters order in MethodAccessor constructor
