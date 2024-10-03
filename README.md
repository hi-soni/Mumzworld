# Question 1: SMS Notifications Solution
The solution will be modular and configurable. The key objectives we are trying to achieve,
1. Abstract Implementation: Module should allow easy integration for different SMS gateways. So as and when required SMS service provider can be replaced.
2. Configurable & Dynamic enough: Enable the admin to configure different message templates for events like order creation, shipment creation, etc. And it should allow dynamic variables like customer name, order number in SMS templates.
3. History & Status: Record all sent messages and their statuses, including error tracking for failed messages.
4. High Throughput: Module should be able to manage large number of messages.

**Solution Architecture**
In order to achieve above requirement, we need to create a custom module considering below features,

**1. Abstraction Layer**
The abstract layer will define all necessary methods, so while integrating SMS service provide APIs, it will provide template/signature,
**i.e**
Interface: SmsServiceInterface
Methods:
  1. sendSMS
  2. getSMSStatus

A SmsServiceInterface interface must implement, this makes easy to replace/add new SMS service provider.

**2. Configurable SMS Templates**
Admin will be able to define templates for different events (e.g., Order Created, Shipment Created) via the Magento admin. There can be two ways,
  1. System configuration: we can use system configuration setting. This will allow us to set different templates for each website/store/store view, so templates can be managed with different language even.
  
  **NOTE**: this is recommended way if we have limited templates and events.
  
  2. Template Management: we can create a dedicated section, where we can manage different templates based on events, website, store & store view, customer group, segmentation.
  
  **NOTE**: This solution is suitable for complex combination, like customer group, segmentation, scheduling template based on date & time.

**3. SMS Parser for Handling Templates and Messages**
We need to create a SmsParser class responsible for selecting the appropriate message template, parsing dynamic variables, and return the final message.

**4. Event Listeners for Events**
Using the event observer pattern, we can identify orders creation, order cancelation, shipments, shipment tracker create/update etc. We'll listen to these events and trigger the SMS notification logic accordingly.

**5. Handling High Volume of Messages**
To handle high transaction volumes (e.g., 1k messages/hour), we can use:
- Use asynchronous processing (A message queue system) to queue and process SMS notifications.
- Implement rate limiting the SMS gateway has a limit on the number of messages per minute/hour.
Magento has built-in support for message queues. We will use it to queue the messages and process them asynchronously.

**6. Logging and Status Tracking**
We need to create a database table to log each SMS sent, along with its status and if there are any error details. This table can be exposed via the admin UI for review.
Log clear: since we are expecting a high volume of messages, there are high chances that log table will have huge number of records. So, we should create a CRON job, that will delete/archive records after predefined duration.

**Summary:**
- Gateway Abstraction: Allows switching SMS providers without changing core logic.
- Configurable Templates: Admin can configure SMS templates for different events and use dynamic variables.
- Logging: A database-backed logging system tracks each SMS sent and its status.
- High Volume Handling: Message queueing allows high-volume processing of SMS notifications in an asynchronous manner, ensuring scalability.

# Question 4: Price Drop Notification
The solution will allow customers to opt-in for price drop notifications, configure thresholds for price drops, and process notifications efficiently. I'll also detail how the data will be stored, processed, and how it can be used for both logged-in and guest users.

### Key Requirements Breakdown
1. Global and Product-level Enable/Disable Feature: There should be configuration to enable/disable subscription at product level or globally.
2. Admin-defined Price Drop Thresholds: Admin can define thresholds for price drop, to avoid sending email for minor price change.
3. Customer Opt-in/Opt-out functionality: Customer should subscribe if feature is enabled for particular product or globally.
4. Email Notifications for Price Drops: Admin can define/configure email template for price drop email notification.
5. GraphQL API support: user can opt-in/opt-out via GraphQL APIs.
   
## Solution Overview

**1. Global Configuration**
Below configuration options will be available for Admin to control features based on business requirement.
  - Global Enable/Disable: Introduce a system configuration setting in the admin panel that controls whether the price drop notification feature is enabled globally for the website.
  - Price Drop Thresholds: Admins can define a threshold percentage (e.g., >10%) for when notifications should be sent. This setting will be stored in system configuration and applied across the system.
  - Email Template: This configuration will allow admin to select email template to be used for sending price drop email notification.

**2. Product-Specific Feature Control**
  - Product-Specific Enable/Disable: Each product will have an additional product attribute (price_drop_enabled) that determines if notifications are available for that product. This attribute will be available for all product types and can be managed by the admin.

**3. Email Notifications**
  - When a price drop occurs, notifications will be sent out based on templates (configurable by admin). This will use Magento’s email functionality, with support for custom variables like customer name, product name, old price, and new price.

**4. Customer Opt-in/Opt-out**
  - Opt-in/Opt-out Process: Customers will be able to opt-in to receive price drop notifications on the product page via the headless React frontend. A "Price Drop Alert" button will allow users to subscribe to the feature. For logged-in users, this will be tied to their customer account. For guest users, we’ll store their email and track their preferences based on the product ID.
  - GraphQL: We will expose GraphQL mutations to opt-in and opt-out from price notifications.

## Solution Architecture

**1. Global Configuration for Price Drop Notification**

**Admin Configuration**: We'll introduce a configuration option in Stores > Configuration > Catalog to enable/disable the feature globally.

````
// app/code/Mumzworld/PriceDropNotification/etc/adminhtml/system.xml
<system>
    <section id="catalog">
        <group id="price_drop_notification" translate="label">
            <label>Price Drop Notification</label>
            <field id="enabled" translate="label" type="select" sortOrder="10" showInDefault="1" showInWebsite="1" showInStore="1">
                <label>Enable Price Drop Notification</label>
                <source_model>Magento\Config\Model\Config\Source\Yesno</source_model>
            </field>
            <field id="threshold" translate="label" type="text" sortOrder="20" showInDefault="1" showInWebsite="1" showInStore="1">
                <label>Price Drop Threshold (%)</label>
            </field>
        </group>
    </section>
</system>
````

**2. Product Attribute - Price Drop Notification**

**Product Attribute**: The price_drop_enabled attribute will be added to each product via a Data Patch.
````
//app/code/Mumzworld/PriceDropNotification/Setup/Patch/Data/AddPriceDropAttribute.php
public function apply()
{
    $eavSetup = $this->eavSetupFactory->create();
    $eavSetup->addAttribute(
        \Magento\Catalog\Model\Product::ENTITY,
        'price_drop_enabled',
        [
            'type' => 'int',
            'label' => 'Enable Price Drop Notification',
            'input' => 'boolean',
            'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
            'required' => false,
            'default' => 0,
            'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
            'visible_on_front' => true,
        ]
    );
}
````

**3: Customer Opt-In/Opt-Out Process**

**Opt-In/Opt-out GraphQL Mutation**: Customers can opt-in or opt-out using GraphQL. For logged-in users, this will be tied to the customer ID. For guest users, the system will associate the opt-in with the email address and product ID.
````
mutation {
  subscribePriceDrop(productId: 1) {
    success
    message
  }
}

mutation {
  unsubscribePriceDrop(productId: 1) {
    success
    message
  }
}
````

**Opt-In Resolver**: The corresponding resolver would handle the logic of subscribing users.
````
// app/code/Mumzworld/PriceDropNotification/Model/Resolver/SubscribePriceDrop.php
public function resolve($field, $context, ResolveInfo $info, array $value = null, array $args = null)
{
    $productId = $args['productId'];
    $customerId = $this->getCustomerIdFromContext($context);
    $email = $context->getUserId() ? $this->getCustomerEmail($customerId) : $args['email'];

    $this->priceDropSubscriptionService->subscribe($productId, $email);
    return ['success' => true, 'message' => 'Successfully subscribed'];
}
````

**Opt-Out Resolver**: The corresponding resolver would handle the logic of unsubscribing users.
````
// app/code/Mumzworld/PriceDropNotification/Model/Resolver/UnsubscribePriceDrop.php
public function resolve($field, $context, ResolveInfo $info, array $value = null, array $args = null)
{
    $productId = $args['productId'];
    $customerId = $this->getCustomerIdFromContext($context);

    // For guest users, use their email address from the context
    $email = $context->getUserId() ? $this->getCustomerEmail($customerId) : $args['email'];

    // Unsubscribe customer or guest user
    $this->priceDropSubscriptionService->unsubscribe($productId, $customerId, $email);

    return ['success' => true, 'message' => 'Successfully unsubscribed'];
}
````

**Business Logic for Opt-In / Opt-Out**: A service will manage the subscribe and unsubscribe process. This service will locate the customer's or guest’s subscription in the database and mark it as active/inactive.
````
//app/code/Mumzworld/PriceDropNotification/Model/Service/PriceDropSubscriptionService.php
public function subscribe($productId, $customerId = null, $email = null)
{
    $subscription = $this->subscriptionRepository->getByProductAndCustomerOrEmail($productId, $customerId, $email);
    
    if (!$subscription) {
        $subscription->setIsActive(1); // Mark subscription as inactive
        $this->subscriptionRepository->save($subscription);
    } else {
        throw new \Magento\Framework\Exception\LocalizedException(__('Already Subscribed.'));
    }
}

public function unsubscribe($productId, $customerId = null, $email = null)
{
    $subscription = $this->subscriptionRepository->getByProductAndCustomerOrEmail($productId, $customerId, $email);
    
    if ($subscription) {
        $subscription->setIsActive(0); // Mark subscription as inactive
        $this->subscriptionRepository->save($subscription);
    } else {
        throw new \Magento\Framework\Exception\LocalizedException(__('Subscription not found.'));
    }
}
````

**4: Business logic** - Identify if Price Drop, Store data to process & Send email for price drop notification.

**Observer to Log Price Drops**
When the product price is updated, the price drop handler will check if the drop exceeds the configured threshold and store it into custom table [price_drop_log].
We will be using catalog_product_save_after observer, it will be triggered after product save, will use this event to compare if product price be dropped beyond threshold limit. If so, data will be stored into custom table price_drop_log.
````
// app/code/Mumzworld/PriceDropNotification/Observer/PriceDropObserver.php
public function execute(Observer $observer)
{
    $isEnable = $this->scopeConfig->getValue('catalog/price_drop_notification/enable, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    If(!$isEnable){
      return true;        
    }

    // Retrieve product and check price
    $product = $observer->getProduct();
    $newPrice = $product->getPrice();
    $oldProduct = $this->productRepository->getById($product->getId());
    $oldPrice = $oldProduct->getPrice();

    // Calculate price drop
    $priceDropPercentage = (($oldPrice - $newPrice) / $oldPrice) * 100;
    $threshold = $this->scopeConfig->getValue('catalog/ price_drop_notification /price_drop_threshold', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

    if ($priceDropPercentage >= $threshold) {
        // Log price drop to custom table
        $connection = $this->resourceConnection->getConnection();
        $connection->insert('price_drop_log', [
            'product_id' => $product->getId(),
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'price_drop_percentage' => $priceDropPercentage,
            'status' => 0, // Not processed yet
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
````

**Cron Job to Process Price Drops**

We will create a cron job that periodically checks the price_drop_log table for unprocessed price drops and triggers the notification system if necessary. In order to handle high volume, it will identify records an queue data to send price drop notification.
Cron job can be configured to process once or trice a day, based on business requirement. 
````
// app/code/Mumzworld/PriceDropNotification/Cron/ProcessPriceDrops.php
public function execute()
{
        $connection = $this->resourceConnection->getConnection();
        $priceDropTable = $connection->getTableName('price_drop_log');

        // Fetch unprocessed price drops
        $priceDrops = $connection->fetchAll("SELECT * FROM {$priceDropTable} WHERE status = 0");
        foreach ($priceDrops as $priceDrop) {
            // Send notifications for the product
            $this->notificationSender->sendPriceDropNotification($priceDrop['product_id'], $priceDrop['price_drop_percentage']);

            // Mark price drop as processed
            $connection->update(
                $priceDropTable,
                ['status' => 1],
                ['id = ?' => $priceDrop['id']]
            );
        }
    }
````

**Message Queue for Email Notification**

For scalability, email notifications should be sent asynchronously using Magento’s Message Queue system.
````
<!-- app/code/Mumzworld/PriceDropNotification/etc/queue.xml -->
<queue xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework-message-queue:etc/queue.xsd">
    <topic name="price_drop_notification" consumerInstance="Mumzworld\PriceDropNotification\Model\Consumer\NotificationConsumer" />
</queue>
````

````
// app/code/Mumzworld/PriceDropNotification/Model/NotificationSender.php
namespace Mumzworld\PriceDropNotification\Model;
use Magento\Framework\MessageQueue\PublisherInterface;

class NotificationSender
{
    protected $publisher;
    public function __construct(PublisherInterface $publisher)
    {
        $this->publisher = $publisher;
    }
    public function sendPriceDropNotification($productId, $priceDropPercentage)
    {
        // Publish notification to the queue
        $this->publisher->publish('price_drop_notification', json_encode([
            'product_id' => $productId,
            'price_drop_percentage' => $priceDropPercentage
        ]));
    }
}
````

**5: Price Drop Notification GraphQL Queries**

We will expose GraphQL queries to get the price drop status and opt-in status for the user. Below is an example of the query to check subscription status.
````
query {
    priceDropStatus(productId: 1) {
        isSubscribed
        product {
          id
          name
          price
        }
      }
}
````

**6: Logged-in vs Guest Customer Handling**

**Logged-in Customers**: Subscription data will be tied to the customer’s account using their customer ID. Notifications will be tracked in relation to the customer entity.
**Guest Customers**: For guests, we’ll store their subscription with their email address and product ID. If the guest checks out and creates an account later, we can migrate their subscriptions to their new account.

### Data Storage & Processing

**Subscriptions**: We'll create a custom table price_drop_subscriptions to store customer subscriptions. Each entry will store customer_id (for logged-in users), email (for guests), and product_id.

````
// Store customer & product subscription data.
CREATE TABLE price_drop_subscriptions (
    subscription_id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_id INT,
    email VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
````

**Process data**: We'll create a custom table price_drop_log to store price dropped product. 
````
CREATE TABLE price_drop_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    old_price DECIMAL,
    new_price DECIMAL,
    price_drop_percentage DECIMAL,
    status TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
````
### High-Volume Considerations
To handle high volumes, the email sending process will be handled by Magento’s built-in message queue system. This will allow asynchronous processing, ensuring that large numbers of notifications can be handled without performance degradation.
