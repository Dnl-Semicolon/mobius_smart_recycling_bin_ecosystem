Implementation Spec: Wide Events / Canonical Log Lines

Overview
Modern distributed systems generate thousands of fragmented log lines per request that are impossible to correlate. The solution is wide events: emit one context-rich, structured event per request per service containing all debugging context, then make sampling decisions at the tail (after outcome is known).

Mental Model Shift
Old model: Log what your code is doing (debug diary scattered across files)
New model: Log what happened to this request (single structured business record)
Core principles (10 bullets max):

One event per request per service—not dozens of log statements
Build event throughout request lifecycle, emit once at completion
High cardinality is valuable (user_id, request_id)—not a cost problem
High dimensionality is valuable (50+ fields)—enables arbitrary queries
Structured logging ≠ wide events; structured is JSON format, wide is philosophy
OpenTelemetry is plumbing, not a solution—you must add business context deliberately
Tail sampling keeps 100% of errors/slow requests, samples success
Wide events power both debugging AND dashboards—same data, different views
Context that exists in code should exist in the event (cart value, feature flags, retry count)
Query analytics replaces grep—SQL over structured data, not string search


Implementation Pattern
Definition: Wide Event / Canonical Log Line
A wide event is a single, high-dimensionality JSON object emitted exactly once per request per service hop, containing all request metadata, user context, business objects, infrastructure details, and outcome information needed to debug any issue without correlating multiple log lines. It serves as the authoritative record of what happened during that request.
Pseudo-code: Middleware → Build → Enrich → Emit
FUNCTION wideEventMiddleware(request, response, next):
    startTime = NOW()
    
    // STEP 1: Initialize event with request/infra context
    event = {
        request_id:    getOrGenerateRequestId(request),
        trace_id:      getTraceId(request),
        span_id:       generateSpanId(),
        timestamp:     ISO8601(NOW()),
        method:        request.method,
        path:          request.path,
        query_params:  request.queryString,
        client_ip:     request.ip,
        user_agent:    request.headers["User-Agent"],
        service:       ENV.SERVICE_NAME,
        version:       ENV.SERVICE_VERSION,
        deployment_id: ENV.DEPLOYMENT_ID,
        region:        ENV.REGION,
        environment:   ENV.ENVIRONMENT
    }
    
    // STEP 2: Attach event to request context for enrichment
    request.context.wideEvent = event

    TRY:
        // STEP 3: Execute handler (handler enriches event)
        await next()
        
        event.status_code = response.status
        event.outcome = "success"
    
    CATCH error:
        event.status_code = 500
        event.outcome = "error"
        event.error = {
            type:      error.name,
            code:      error.code,
            message:   error.message,
            retriable: error.retriable ?? false,
            stack:     IF debug THEN error.stack ELSE null
        }
        RETHROW error
    
    FINALLY:
        // STEP 4: Finalize timing and emit ONCE
        event.duration_ms = NOW() - startTime
        event.response_size_bytes = response.contentLength
        
        IF shouldSample(event):
            logger.emit(event)


// Handler enrichment pattern
FUNCTION checkoutHandler(request):
    event = request.context.wideEvent
    user = request.context.user
    
    // Enrich: User context (non-PII)
    event.user = {
        id:             user.id,
        session_id:     user.sessionId,
        subscription:   user.plan,
        account_age_days: daysSince(user.createdAt),
        org_id:         user.orgId,
        role:           user.role
    }
    
    // Enrich: Feature flags
    event.feature_flags = getActiveFlags(user)
    
    // Enrich: Business objects
    cart = await getCart(user.id)
    event.cart = {
        id:          cart.id,
        item_count:  cart.items.length,
        total_cents: cart.total,
        currency:    cart.currency
    }
    
    // Enrich: Dependency timing
    paymentStart = NOW()
    result = await paymentGateway.charge(cart)
    event.dependencies = event.dependencies ?? []
    event.dependencies.push({
        name:       "stripe",
        type:       "http",
        latency_ms: NOW() - paymentStart,
        success:    result.success
    })
    
    // Enrich: Error details if failed
    IF result.error:
        event.error = {
            type:         "PaymentError",
            code:         result.error.code,
            message:      result.error.message,
            retriable:    result.error.retriable,
            decline_code: result.error.stripeDeclineCode
        }
    
    RETURN response

Wide Event Schema (JSON)
json{
  "_meta": {
    "schema_version": "1.0",
    "emitted_at": "2025-01-15T10:23:45.612Z"
  },
  
  "trace": {
    "request_id": "req_8bf7ec2d",
    "trace_id": "abc123def456789",
    "span_id": "span_001",
    "parent_span_id": "span_000"
  },
  
  "service": {
    "name": "checkout-service",
    "version": "2.4.1",
    "deployment_id": "deploy_789",
    "git_sha": "a1b2c3d",
    "environment": "production",
    "region": "us-east-1",
    "host": "checkout-7b8f9-xyz",
    "container_id": "ctr_abc123"
  },
  
  "http": {
    "method": "POST",
    "path": "/api/v1/checkout",
    "route": "/api/v1/checkout",
    "query_params": "source=mobile",
    "status_code": 500,
    "request_size_bytes": 1247,
    "response_size_bytes": 512,
    "client_ip": "192.168.1.42",
    "user_agent": "Mozilla/5.0..."
  },
  
  "timing": {
    "timestamp": "2025-01-15T10:23:45.612Z",
    "duration_ms": 1247,
    "outcome": "error"
  },
  
  "user": {
    "id": "user_456",
    "session_id": "sess_abc123",
    "org_id": "org_789",
    "role": "admin",
    "subscription_tier": "premium",
    "account_age_days": 847,
    "is_internal": false
  },
  
  "auth": {
    "method": "jwt",
    "scopes": ["read", "write"],
    "token_age_seconds": 3600
  },
  
  "business": {
    "order": {
      "id": "order_xyz",
      "type": "standard"
    },
    "cart": {
      "id": "cart_123",
      "item_count": 3,
      "total_cents": 15999,
      "currency": "USD",
      "coupon_code": "SAVE20"
    },
    "custom": {}
  },
  
  "flags": {
    "feature_flags": {
      "new_checkout_flow": true,
      "express_payment": false
    },
    "experiments": {
      "checkout_variant": "B",
      "cohort": "test_group_2"
    }
  },
  
  "error": {
    "type": "PaymentError",
    "code": "card_declined",
    "message": "Card declined by issuer",
    "retriable": false,
    "decline_code": "insufficient_funds",
    "stack": null
  },
  
  "dependencies": [
    {
      "name": "postgres",
      "type": "db",
      "latency_ms": 45,
      "success": true,
      "query_count": 3
    },
    {
      "name": "redis",
      "type": "cache",
      "latency_ms": 2,
      "success": false,
      "cache_hit": false
    },
    {
      "name": "stripe",
      "type": "http",
      "latency_ms": 1089,
      "success": false,
      "attempt": 3,
      "endpoint": "/v1/charges"
    }
  ]
}
```

---

## Tail Sampling Rules
```
FUNCTION shouldSample(event) -> boolean:
    // RULE 1: Always keep errors (100%)
    IF event.timing.outcome == "error":
        RETURN true
    IF event.http.status_code >= 500:
        RETURN true
    IF event.error IS NOT NULL:
        RETURN true
    
    // RULE 2: Always keep slow requests (above p99 threshold)
    IF event.timing.duration_ms > P99_THRESHOLD_MS:
        RETURN true
    
    // RULE 3: Always keep VIP/enterprise users
    IF event.user.subscription_tier IN ["enterprise", "premium"]:
        RETURN true
    
    // RULE 4: Always keep flagged feature rollouts (for debugging)
    IF event.flags.feature_flags CONTAINS any DEBUG_FLAGS:
        RETURN true
    
    // RULE 5: Always keep internal/test users
    IF event.user.is_internal == true:
        RETURN true
    
    // RULE 6: Random sample remaining success traffic
    RETURN random() < BASE_SAMPLE_RATE  // e.g., 0.05 for 5%
Thresholds to configure: P99_THRESHOLD_MS, DEBUG_FLAGS[], BASE_SAMPLE_RATE

Operational Notes (PII, costs, retention)
Do

Use opaque IDs (user_id, session_id) rather than PII
Emit exactly once per request (in finally block)
Include all context before it goes out of scope
Add dependency timings inline as calls complete
Use columnar storage (ClickHouse, BigQuery) for cost-effective high-cardinality queries
Set retention tiers: 7d hot (full), 30d warm (sampled), 90d cold (errors only)
Version your schema (schema_version field)
Normalize field names across services

Don't

Log PII: no emails, names, addresses, IPs of EU users, phone numbers
Log secrets: no tokens, passwords, API keys, card numbers
Log full request/response bodies (use truncated hashes if needed)
Create unbounded cardinality: no raw user input as field values
Emit multiple events per request (defeats the purpose)
Use string interpolation for field values (error: ${msg})
Store stack traces in production unless explicitly debugging
Sample before knowing outcome (head sampling loses errors)

Cost Controls

Tail sampling reduces volume 90-95% while keeping 100% of actionable events
Drop debug-level fields in production
Compress JSON (gzip typically 85%+ reduction)
Use fixed-schema columnar formats (Parquet) for cold storage


Open Questions / Assumptions

What is P99 latency threshold? — Needs baseline measurement; start with 2000ms
Which feature flags need 100% sampling? — Define DEBUG_FLAGS list per rollout
Multi-service trace assembly — Assumes trace_id propagation via headers; needs correlation service
Schema evolution — How to handle field additions/deprecations across versions?
Nested business objects — business.custom is escape hatch; define domain-specific schemas
PII in path params — /users/{email} routes leak PII; normalize to /users/{id}
Client-side events — Same pattern applies; needs separate ingestion pipeline
Alerting integration — Wide events can power alerts; define threshold queries