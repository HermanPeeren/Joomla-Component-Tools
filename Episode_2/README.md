# Episode 2: An Embedded Application

## Seblod package
app_cck_event_schedule.zip can be installed as a Joomla extension (on a site where Seblod is installed).

A Seblod template for the event schedule will be added. 

BUT:
I used Seblod in a less common way: by putting every entity entirely in its own table, not using an article as basis. It worked, but some things went wrong: the Locator GroupX field was not visible/readable in the template. Looking for the culprit I saw queries for the GroupX fields, that wanted a client-field to be "intro", where it was "admin". 
The query is created in line 828 of the GroupX-plugin, in the _getChildren() method.

Instead of looking for client = "intro", I changed it into looking for client = $targetClient, which was assigned as "admin" if storage is free (so the GroupX is not stored in the introtext of an article, as is usual in Seblod):

```
// HP 16-8-2024 : if free storage, then list | item client should not be 'intro', but 'admin'.
$targetClient = ($config['location']=='free')? 'admin' : 'intro';
```


