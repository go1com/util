# Elastic search storage structure

From architecture point of view, we want to store data by portal. that mean each portal has their own data. 

#### Too many indices 
To implements its, we created ~5k indices in elastic search cluster. It requires really big resource and as a result it took down our elastic search.

After research we came up with another approach (https://www.elastic.co/guide/en/elasticsearch/guide/master/faking-it.html).

#### Faking Index per Portal with Aliaseses

###### 1. Create indices group by services domain. 

| Index name        | Document Type                                                                |
| ----------------- | -----------------------------------------------------------------------------|
| go1               | portal, account, user, enrolment...                                          |
| go1_activity      | activity                                                                     |
| go1_my_team       | myteam_progress                                                              |

At the moment, we put all of document types into go1 index. we need to split into small domain as soon as possible 

###### 2. Create Fake portal index with elastic search alias

``` json
POST /_aliases
{
    "actions" : [
        {
            "add" : {
                 "indices": ["go1", "go1_activity", "go1_my_team"],
                 "alias" : "go1_portal_4",
                 "filter" : { "term" : { "metadata.instance_id" : "4" } },
                 "search_routing": "4"
            }
        }
    ]
}
```

###### 3. From client point of view
We can query data of particular portal via fake alias 

``` json
GET /go1_portal_4/myteam_progress/_search
```
 
###### 4. From backend service 
We can push data to elastic search via `DocumentRepository`

#### How if a service want to define new document type?
The service has to create new index and maintains it own structure.

For instance, service X want to create a new document type to store sent emails. we will
 
* Create new index `go1_mail`
* Create new document type `mail`
* Update portals alias

``` json
POST /_aliases
{
    "actions" : [
        {
            "add" : {
                 "indices": ["go1", "go1_activity", "go1_my_team", "go1_mail"],
                 "alias" : "go1_portal_4",
                 "filter" : { "term" : { "metadata.instance_id" : "4" } },
                 "index_routing": "4",
            }
        }
    ]
}
```
