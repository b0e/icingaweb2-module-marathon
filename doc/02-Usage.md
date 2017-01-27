<a id="Usage"></a>Usage
=====

Dynamically create hosts for Marathon Groups
---------------------------------------------------

Our first use case are virtual Icinga host objects, one for each of your Marathon
Group. Single instances come and go, it's tricky to monitor them
in a meaningful way. Your Groups are here to stay, it is vital for
your service that they are alive.

This example wants to teach you how to configure Director to automagically do
this for you.

Dynamically create services for Marathon Apps
---------------------------------------------------

The second use case is about how to create service checks for your deployed
Marathon Apps. Especially for Apps using marathon-lb as service discovery
solution. This is not about how to monitor the health status of single tasks or
containers (marathon does this for us).

1. We import all Marathon Apps with into the director.
2. We create Icinga 2 Service Objects (e.g. https) to monitor the availability
   of our Apps. 

### Define a dedicated Import Source

As soon as you installed and enabled this module, a new Import Source will be
available in your Icinga Director web frontend:

1. Please provide the basic credentials and choose if you want to import
   Marathon Groups or Marathon Apps

### Create a Sync Rule

Our Sync Rules are responsible for creating real Icinga objects based on
data imported through one or more Import Sources. So let's create a new
rule:

![Marathon sync rule](img/05_aws_sync_rule.png)

Sync Properties allow you to specify how to treat the various properties
in a granular way:

![Marathon sync properties](img/06_aws_sync_properties.png)

Now you are ready to trigger your first Sync Run. Activity Log and Sync History
will show you what related actions took place

### Have a look at your new hosts

Let's have a look at our newly created host and services:

![Marathon host config](img/07_aws_host_config.png)

In case you want to achieve visibilty for your imported Custom Vars please
define related Fields directly on your Marathon ASG Template. Your hosts could
then look as follows:

![Marathon host vars](img/08_aws_host_config_with_vars.png)

The preview tab shows our rendered host, this is how it will get deployed
to Icinga:

![Marathon host preview](img/09_aws_host_preview.png)

That's all for now, have fun!

