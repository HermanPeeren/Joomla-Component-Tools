# Episode 3: Creating a component

## The component so far
In the folder "com_eventschedule" of this episode you'll find the component so far.
As we've spent most time on the basic backend, you'll find the entities and 
their relations in the administrator as described in the article. 
Also the cascading dynamic select boxes are in this example.

The basic frontend (with the schedule) will be finished the coming days. 

## Relations
Emphasis in this episode was on relations.

It was also an exploration of how core Joomla implements 
entities (and their relations). Main conclusion: **basicly a Table-object 
is the active record implementation of entities**, but it is mainly used for 
storing, updating and deleting the entities. 

Relations are partly handled in Table-objects in core Joomla: 
* n:1 relation: the owning side has the foreign key stored in the database table
* n:n relation: the junction table is handled in the Table-object (a.o. user groups)

**The rest of relations is all handled in the model**. The role of entity is 
therefore divided between the Table-object and the model. 
In Laravel the model is the entity; idem in the new entities package in the framework. 

One of the things I wanted to do is using relations in core Joomla, 
as much as possible in the way core Joomla does it, not using any ORM.

## Cascading dynamic dropdowns with sql-field
It is in fact a little bit simpler than described in the developers manual: 
the reload() method in the controller already  stores all input from the form in the 
user state and based on that the form is rendered again. In our case: 
the sections-dropdown is rendered based on the value of the container-dropdown
(in the locator). No need to explicitly also store the container_id in the user state. 

Will correct it in the developers manual for the sql-field.

## Multiple subform field
In order to automatically store and retrieve a multiple subform field, 
you'll have to put the field name of that subform in the `$this->_jsonEncode array of the Table.
In our case: the locators that are embedded in the event-entity. 
In the EventTable we put:
```php
// Put multiple subform field-name in $this->_jsonEncode array
        if ( ( !empty( $array[ 'locators' ] ) && ( is_array( $array[ 'locators' ] ) ) ) ) {
            $this->_jsonEncode[] = "locators";
        }
```
Will add it to the developers manual for the subform field.
