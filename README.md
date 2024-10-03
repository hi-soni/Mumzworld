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
