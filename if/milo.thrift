namespace java com.milo.thrift
namespace py milo.thrift
namespace php milo

enum EventType {
  CLIENT_SEND = 1,
  CLIENT_RECV = 2,
  SERVER_SEND = 3,
  SERVER_RECV = 4,
  CUSTOM = 5
}

struct Event {
  1: i64 timestamp
  2: EventType event_type
  3: string value
}

struct Span {
  1: optional i64 trace_id
  2: optional string name,
  3: optional i64 id,
  4: optional i64 parent_id,
  5: optional string client_host,
  6: optional string server_host,
  7: optional list<Event> events,
  8: optional map<string, string> annotations
  9: optional map<string, i64> counters
}

# scribe support
enum ResultCode
{
  OK,
  TRY_LATER
}

struct LogEntry
{
  1:  string category,
  2:  binary message
}

service MiloCollector
{
  ResultCode Log(1: list<LogEntry> messages);
}
