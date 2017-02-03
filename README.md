Monolog Integration for Flow
============================

Provides a monolog factory to create loggers from configuration if you need that.
Also replaces all Neos logs (Security, System, Query) with Monolog by default.
To change that and the handlers check the Settings.yaml.

Handlers are configured separately from the loggers and reused by name.
You should generally reference handlers by name in the logger configuration.
For more information about Handlers check also the monolog package.
 
> The monolog format is slightly different than the default Flow log file format, 
> also the configured monolog handler does no log rotation like the Flow log does, 
> so you need to take care of that.