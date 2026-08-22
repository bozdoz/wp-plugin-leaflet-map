if (!window.L) {
  const leaflet = (await import('leaflet_js_esm')).default;

  function isObject(value) {
    return (
      value !== null &&
      (typeof value === 'object' || typeof value === 'function')
    );
  }

  function findProperty(object, property) {
    if (typeof property !== 'string') {
      return null;
    }

    if (Object.hasOwn(object, property)) {
      return { value: object[property], alias: false };
    }

    const proper = property[0].toUpperCase() + property.slice(1);
    if (Object.hasOwn(object, proper)) {
      return { value: object[proper], alias: true };
    }

    const lowerProperty = property.toLowerCase();
    const matchingProperty = Object.keys(object).find(
      (key) => key.toLowerCase() === lowerProperty
    );
    if (matchingProperty) {
      return { value: object[matchingProperty], alias: true };
    }

    return null;
  }

  function proxy(value, constructOnCall) {
    if (!isObject(value)) {
      return value;
    }

    return new Proxy(value, {
      apply(target, thisArg, args) {
        if (constructOnCall) {
          return Reflect.construct(target, args);
        }

        return Reflect.apply(target, thisArg, args);
      },
      get(target, property, receiver) {
        const found = findProperty(target, property);
        if (!found) {
          return Reflect.get(target, property, receiver);
        }

        const descriptor = Object.getOwnPropertyDescriptor(target, property);
        if (
          descriptor &&
          descriptor.configurable === false &&
          descriptor.writable === false
        ) {
          return found.value;
        }

        return proxy(
          found.value,
          found.alias && typeof found.value === 'function'
        );
      },
    });
  }

  window.L = proxy(leaflet, false);
}
