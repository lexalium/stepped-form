# Events

The form can dispatch the following events:
1. **BeforeHandleStep** - will dispatch before step handling. Event contains
   data passed to the handle method, form entity, and step instance.
2. **FormFinished** - will dispatch when there is no next step after handling current
   one. Event contains form entity.
