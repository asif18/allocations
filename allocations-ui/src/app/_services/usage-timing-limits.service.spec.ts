import { TestBed } from '@angular/core/testing';

import { UsageTimingLimitsService } from './usage-timing-limits.service';

describe('UsageTimingLimitsService', () => {
  beforeEach(() => TestBed.configureTestingModule({}));

  it('should be created', () => {
    const service: UsageTimingLimitsService = TestBed.get(UsageTimingLimitsService);
    expect(service).toBeTruthy();
  });
});
