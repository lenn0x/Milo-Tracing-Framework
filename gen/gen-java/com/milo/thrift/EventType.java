/**
 * Autogenerated by Thrift
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 */
package com.milo.thrift;


import java.util.Map;
import java.util.HashMap;
import org.apache.thrift.TEnum;
public enum EventType implements TEnum{
    CLIENT_SEND(1),
    CLIENT_RECV(2),
    SERVER_SEND(3),
    SERVER_RECV(4),
    CUSTOM(5);

  private static final Map<Integer, EventType> BY_VALUE = new HashMap<Integer,EventType>() {{
    for(EventType val : EventType.values()) {
      put(val.getValue(), val);
    }
  }};

  private final int value;

  private EventType(int value) {
    this.value = value;
  }

  /**
   * Get the integer value of this enum value, as defined in the Thrift IDL.
   */
  public int getValue() {
    return value;
  }

  /**
   * Find a the enum type by its integer value, as defined in the Thrift IDL.
   * @return null if the value is not found.
   */
  public static EventType findByValue(int value) { 
    return BY_VALUE.get(value);
  }
}
