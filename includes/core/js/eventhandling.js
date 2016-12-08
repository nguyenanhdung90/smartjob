EventManager =
{
   Registry: [],

   Subscribe: function (eventName, method)
   {
      if (method === undefined) return;

      var events = this.Registry[eventName];
      if (events === undefined)
      {
         this.Registry[eventName] = [];
         events = this.Registry[eventName];
      }
      if (typeof (method) === 'function')
      {
         var methodIndex = -1;
         for (var i = 0; i < events.length; i++)
         {
            if (events[i] === method)
            {
               methodIndex = i;
               break;
            }
         }
         if (methodIndex === -1)
         {
            events.push(method);
         }

      }
   },

   UnSubscribe: function (eventName, method)
   {
      if (method === undefined) return;

      var events = this.Registry[eventName];
      if (events !== undefined)
      {
         var methodIndex = -1;
         for (var i = 0; i < events.length; i++)
         {
            if (events[i] === method)
            {
               methodIndex = i;
               break;
            }
         }
         if (methodIndex !== -1)
         {
            events.splice(methodIndex, 1);
         }
      }
   },

   Publish: function (eventName)
   {
      var events = this.Registry[eventName];
      var args = [];
      var i;
      for (i = 1; i < arguments.length; i++)
      {
         args.push(arguments[i]);
      }

      if (events !== undefined)
      {
         for (i = 0; i < events.length; i++)
         {
            events[i].apply(this, args);
         }
      }
   }

};