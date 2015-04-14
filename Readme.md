# JSON API #

## What does it do? ##

Provides an Interface for data output in JSON format. Support for files (FAL) and any extbase model.

## Users Manual ##

This extension requires the [routing](http://typo3.org/extensions/repository/view/routing) extension written by Xavier Perseguers. 
After installing, you may do some URL Rewriting for the routing eID Query Parameter as suggested in the [Documentation](http://docs.typo3.org/typo3cms/extensions/routing/Introduction/Index.html) of the routing extension.
Be sure to add the line at the top of your htaccess.

The URI pattern for calling the API looks as follows

_action/path/arguments_

The action part is required and has only two possible values: *'list'* and *'single'*

The path part tells the API what type (extbase model) your data has you want to print out and is also required.
The path part has different possible values. See the table below to understand the path-to-model mapping:

| path                       | model                                  |
| -------------------------- | -------------------------------------- |
| my_extkey-mymodel          | Tx_MyExtkey_Domain_Model_Mymodel       |
| MyVendor-my_extkey-mymodel | MyVendor\MyExtkey\Domain\Model\Mymodel |
| news-news                  | Tx_News_Domain_Model_News              |
| yag-album                  | Tx_News_Domain_Model_Album             |
| NN-nn_address-person       | NN\NnAddress\Domain\Model\Person       |
| files                      | TYPO3\CMS\Core\Resource\File           |

The single action additionally requires an argument - the uid of the record you want to fetch.

Additionally, the API accepts the following query parameters to control the output:
  * orderBy (must be set to a property name of the model)
  * orderDirection ('asc' or 'desc')
  * limit (integer > 0)
  * storagePages (csv-list of page uid values)
  * directory (path=files ONLY). 

See examples below to understand how to call the API properly

 * get all albums _routing/json_api/list/yag-album/?orderBy=date&orderDirection=desc_
 * list all news in pages with ids 189,214: _routing/json_api/list/news-news/?orderBy=datetime&orderDirection=desc&storagePages=189,214_
 * get the person with uid 46: _routing/json_api/single/NN-nn_address-person/46_
 * get 10 files from fileadmin/user_upload/my_folder: _routing/json_api/list/files/?directory=fileadmin/user_upload/my_folder/&limit=10_

### Configuration ###

The configuration is done via TypoScript.
The extension provides a configuration for FAL files, however, any Extbase Model you want to retrieve through the API must be configured first.
The configuration syntax is based on the configuration for extbase's JsonView.

```ts
plugin.tx_jsonapi {
  settings {
    output {
      config {
        MyVendor\MyExtension\Domain\Model\MyModel {
          # only render these properties
          _only = property1, property2, property3, property4
          # exclude these properties from rendering
          _exclude = property5, property6
          # descend properties which are descendible
          _descend {
            # property2 is of type datetime and therefore it must be descended
            property2 {}
            # property3 is an array, so descend all entries
            property3 {
              _descendAll {
                # here we can use _only, _descend, and _exclude again
              }
            }
          }
        }
        # Example: Model of ext news
        Tx_News_Domain_Model_News {
          _only = uid, pid, istopnews, title, teaser, bodytext, datetime, falMedia, falRelatedFiles
          _descend {
          	datetime  {}
          	falMedia {
          		_descendAll {
          			_only = uid, pid, title, alternative, description, link, originalResource
          			_descend {
          				originalResource {
          					_only = publicUrl
          				}
          			}
          		}
          	}
          	falRelatedFiles < .falMedia
          }
        }
      }
    }
  }
}
```


